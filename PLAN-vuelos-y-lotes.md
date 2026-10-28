# Plan: registro/entrega por lotes, vuelos del XML FIDS y alertas

Plan de implementación para el requerimiento de [Equipaje-sobredimensionado.md](Equipaje-sobredimensionado.md). Todas las decisiones de este documento ya fueron confirmadas con el cliente. Si algo no está aquí, **pregunta antes de suponer**.

Stack: PHP 8.3 con un framework MVC propio (`framework/`), MySQL (`opain_maletas`), jQuery, Bootstrap 5 y Quagga para leer códigos con la cámara. La zona horaria ya está fijada en `America/Bogota` (`app/bootstrap.php:23`). Las horas del XML también son de Bogotá y vienen sin offset.

---

## 1. Contexto: cómo funciona hoy

- **Registro** (`/administracion/maletas/registrar`, `maletasController::storeAction`): se escanea **una** etiqueta de 10 dígitos. La aerolínea sale de los dígitos 2 a 4 (`codigo.substring(1,4)`, en `public/skins/administracion/js/main.js`) y se busca en `aerolineas.aerolinea_codigo` (código numérico de 3 dígitos, ej. `001` = American). Se puede marcar "maleta de conexión" con otra aerolínea. Al guardar se envían un correo y WhatsApp (Dorabot) por cada teléfono de la aerolínea.
- **Reclamo** (`/administracion/maletas/reclamar`, `reclamoUpdateAction`): se escanea **una** maleta, se marca `maleta_estado='1'` con la fecha y hora de salida, y se notifica.
- **Tipo over**: al iniciar sesión, los usuarios de nivel 3, y los de nivel 2 con `user_puedeover=1`, eligen en `/administracion/index/seleccionarover` el tipo: `over=1` + `step=N|I`, `over=2` o `over=3`. Queda guardado en la sesión (`over`, `step`). `Maletas::insert()` lee la sesión y guarda `maleta_over_tipo` y `maleta_over_lugar`. Una maleta normal tiene `maleta_over_tipo` vacío o NULL.
- **Pantallas públicas** (`/page/index/noreclamadas`, `noreclamadasoveruno|dos|tres`): se refrescan solas con AJAX (`public/skins/page/js/main.js` → `getNoReclamadas*` en `app/modules/page/Controllers/indexController.php`).
- **Flujo aparte** `/page/index/reclamos` → `page/indexController::updateAction`: reclamo individual para el nivel 3 desde el módulo `page`. **No se modifica**, pero se debe verificar que siga funcionando.
- Las notificaciones de WhatsApp ya están unificadas en `maletasController::notificarDorabot($phone, $evento, $aerolinea, $codigo, $overTipo)`, con el token y las plantillas como propiedades de la clase.

## 2. Decisiones confirmadas

| Tema | Decisión |
|---|---|
| Lotes | Se pueden escanear varias maletas, tanto en **registro** como en **reclamo**, para maletas **normales y over**. |
| Asociar vuelo | **Solo en el registro.** En el reclamo se muestra el vuelo que ya tiene la maleta. |
| Vuelo obligatorio | Sí. Si el vuelo no aparece, el operador marca **"Vuelo no listado"** y escribe el número a mano. Esa maleta queda sin hora de salida y **sin alerta**. |
| Mezclar aerolíneas en un lote | Sí. Cada fila muestra los vuelos de su aerolínea. Se pueden seleccionar varias filas de la misma aerolínea y asignarles el mismo vuelo de una vez. |
| Aerolínea para la lista de vuelos | La de **conexión** si la maleta es de conexión; si no, la de la etiqueta. |
| Codeshare | Se muestra cualquier fila del XML cuyo `linecode` sea el de la aerolínea, aunque la opere otra. Ej.: IB 6946 aparece para Iberia aunque el avión sea el AV 8484. |
| Relación código de 3 dígitos ↔ IATA | Columna nueva `aerolineas.aerolinea_iata`, editable en el admin de aerolíneas, con valores precargados (ver §4.1). |
| Filtros del XML | `adi="D"` (salida), `terminal code="TU"`, `qualifier code="S"` y status distinto de `DEP` (despegó) y de `CAN` (cancelado). TU excluye el Puente Aéreo (TP) y la terminal de carga (TC). |
| Nacional o internacional | Por defecto según el tipo de maleta: normal → `D`; over 1 N → `D`; over 1 I → `I`; over 2 → `I`; over 3 → `D`. Hay un botón **"Ver todos los vuelos"** que quita este filtro (necesario, por ejemplo, para una maleta normal de American, que solo tiene vuelos internacionales). |
| Rango de horas | Por defecto, **hoy** (hasta las 23:59). Un botón **"Próximas 24 h"** amplía el rango. |
| Vuelos ya salidos | Se muestra el vuelo si `schedule >= ahora` **o** si `actual >= ahora` y el status no es DEP/CAN (vuelos demorados; ej. un vuelo con `schedule` 18:15 y `actual` 02:55 del día siguiente). |
| Hora de salida para calcular | **`actual`** (hora actualizada). En la interfaz se muestra la programada y la actualizada cuando son distintas. |
| Notificaciones del lote | **Una por maleta**, igual que hoy, con las mismas plantillas y correos. |
| Alerta de salida próxima | Se envía cuando faltan **N minutos** (configurable, **60 por defecto**) para la salida (`actual`) de un vuelo que tiene maletas sin reclamar. **Una sola vez por vuelo.** El correo va al destinatario según el tipo de maleta (normal → `aerolinea_correo`, over → `aerolinea_correo_over`) y lista todas las maletas pendientes de ese vuelo. El WhatsApp queda **preparado pero apagado** hasta que exista la plantilla en Botmaker. |
| Sincronización | Un cron externo (**cron-job.org**, que es el que usa la empresa) llama cada minuto a una URL protegida con token. Alternativa: si los datos tienen más de 3 minutos, se sincroniza al consultar vuelos. |
| Pantallas públicas | Se agregan las columnas **Vuelo, Salida y Tiempo restante**, y se resaltan las maletas cuyo vuelo está por salir. Se **quitan los degradados** de los encabezados de las pantallas over (ver §8). |
| Listado del admin | Mismas columnas, más **Margen al registrar**, resaltado de las urgentes y filtro por vuelo. |
| Tiempo disponible al registrar | Se guarda en minutos al registrar (`salida - registro`) y se muestra en el admin. |
| Fuera de alcance | La simplificación del login (ya se ajustó), la tabla `maletas_over` (vacía y sin uso) y el flujo `/page/index/reclamos` (solo se verifica). |

## 3. XML: cómo leerlo y por qué

- URL: `https://fidsaws.opain.co/eclipsx.xml`, con Basic Auth (usuario y contraseña en [Equipaje-sobredimensionado.md](Equipaje-sobredimensionado.md)). Medido el 27/10/2026: ~686 KB, ~976 filas, responde en ~0,6 s.
- **No convertirlo a JSON.** Se lee con `SimpleXML`, que viene incluido (`php -m` muestra SimpleXML y XMLReader), y se guarda en una tabla de MySQL. JSON se usa solo en las respuestas AJAX al navegador. Con este tamaño, SimpleXML sobra; no hace falta XMLReader.
- **No consultar el XML en cada request.** Hay varios escáneres y 4 pantallas refrescándose, y las alertas tienen que funcionar aunque nadie esté usando la app. Por eso se usa un cron que guarda en la base de datos, y las consultas leen de MySQL.
- Las credenciales van en `app/config/config.php`, por entorno (`$config[env]['fids'] = ['url'=>..., 'user'=>..., 'password'=>...]`), igual que la configuración de la base de datos. No se escriben en el código.

Estructura de una fila (todas tienen los mismos campos):

```xml
<row estimated="" id="2284965" adi="D" schedule="2026-10-27T07:05:00" actual="2026-10-27T07:32:00">
  <line>Avianca</line> <linecode>AV</linecode> <number>8484</number>
  <gate>D13</gate>
  <qualifier code="S">Scheduled</qualifier>
  <flighttype code="D">Domestic</flighttype>          <!-- D | I | H (hangar) -->
  <terminal code="TU">Unified Terminal (New Terminal)</terminal>  <!-- TU | TP | TC -->
  <city-es code="SMR" order="1">Santa Marta</city-es> <!-- puede haber 2 (escalas) -->
  <status-es code="DEP">Despegó</status-es>           <!-- SCH ONT EAL DLY BOR SAL CLS DEP CAN LND -->
</row>
```

Notas:
- `id` **no es único**: los codeshares comparten `id` (hay 111 grupos). La clave única es `(id, linecode, number)`.
- `estimated` siempre viene vacío; usar `actual`.
- Si hay 2 `city-es`, se unen con " / " en orden de `order`.
- Con los filtros aplicados quedan ~477 filas por día, de 27 aerolíneas.

## 4. Base de datos

Crear `sql/2026-10-vuelos-lotes.sql` con todo lo de esta sección (las tablas actuales son MyISAM/latin1; las nuevas pueden ser InnoDB/utf8mb4).

### 4.1 `aerolineas`
```sql
ALTER TABLE aerolineas ADD aerolinea_iata VARCHAR(3) NULL AFTER aerolinea_codigo;
UPDATE aerolineas SET aerolinea_codigo = '019' WHERE aerolinea_codigo = '&nbsp;019'; -- dato corrupto de Satena
```
Precarga con los códigos IATA públicos. Los que aparecen en el XML de ejemplo coinciden con su `linecode`; NK, H2, PU y WK no aparecían en esa muestra.

| código | aerolínea | IATA | | código | aerolínea | IATA |
|---|---|---|---|---|---|---|
| 001 | American | AA | | 220 | Lufthansa | LH |
| 006 | Delta | DL | | 230 | Copa | CM |
| 014 | Air Canada | AC | | 235 | Turkish | TK |
| 016 | United | UA | | 246 | CLIC | VE |
| 019 | Satena | 9R | | 333 | Viva aerobus | VB |
| 036 | Volaris | Y4 | | 487 | Spirit | NK |
| 044 | Aerolíneas Argentinas | AR | | 605 | Sky Airline | H2 |
| 045 | Latam | LA | | 663 | Plus Ultra | PU |
| 057 | Air France | AF | | 742 | Avior | 9V |
| 074 | KLM | KL | | 782 | Laser | QL |
| 075 | Iberia | IB | | 919 | Arajet | DM |
| 127 | GOL | G3 | | 945 | Edelweiss | WK |
| 134 | Avianca | AV | | 973 | Jetsmart | JA |
| 139 | Aeroméxico | AM | | 996 | Air Europa | UX |
| 176 | Fly Emirates | EK | | 000 | Wingo | P5 |

**Dejar en NULL y reportar al cliente para que los complete**: `035 Latam`, `544 Latam`, `999 NN Avianca`, `067 Turpial`, `111 Prueba` y `222 Prueba 2`. Una aerolínea sin IATA no muestra vuelos; el operador usa "Vuelo no listado".

> Ojo: en el XML, `VB` aparece en codeshare con vuelos de Avianca. Pide al cliente que confirme que la aerolínea 333 corresponde a ese `VB`.

### 4.2 Tabla nueva `vuelos`
```sql
CREATE TABLE vuelos (
  vuelo_id INT AUTO_INCREMENT PRIMARY KEY,
  vuelo_xml_id VARCHAR(20) NOT NULL,
  vuelo_linecode VARCHAR(3) NOT NULL,
  vuelo_numero VARCHAR(10) NOT NULL,
  vuelo_aerolinea_nombre VARCHAR(100),
  vuelo_schedule DATETIME NOT NULL,
  vuelo_actual DATETIME NULL,
  vuelo_tipo CHAR(1) NOT NULL,            -- D | I
  vuelo_destino VARCHAR(150),
  vuelo_destino_codigo VARCHAR(10),
  vuelo_puerta VARCHAR(10),
  vuelo_status VARCHAR(5),
  vuelo_status_texto VARCHAR(50),
  vuelo_actualizado DATETIME NOT NULL,
  UNIQUE KEY uk_vuelo (vuelo_xml_id, vuelo_linecode, vuelo_numero),
  KEY idx_linecode_salida (vuelo_linecode, vuelo_schedule)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```
- Solo se guardan las filas con `adi=D`, `TU` y `S`. Las que tienen status `DEP`/`CAN` **también se guardan** (para actualizar su estado); el filtro se aplica al consultar.
- Se usa upsert (`INSERT ... ON DUPLICATE KEY UPDATE`) para que `vuelo_id` no cambie y las maletas sigan apuntando al mismo vuelo.
- En cada sincronización se borran los vuelos con `vuelo_schedule < NOW() - INTERVAL 3 DAY`. Las maletas guardan una copia de los datos del vuelo (§4.4), así que no se pierde nada.

### 4.3 Tabla nueva `vuelos_sync` (una sola fila)
`sync_id`, `sync_ultimo_intento DATETIME`, `sync_ultimo_ok DATETIME`, `sync_filas INT`, `sync_error TEXT`. Sirve para la alternativa de §5 y para diagnosticar problemas.

### 4.4 `maletas`: columnas nuevas
```sql
ALTER TABLE maletas
  ADD maleta_vuelo_id INT NULL,                 -- vuelos.vuelo_id (NULL si es manual)
  ADD maleta_vuelo_numero VARCHAR(15) NULL,     -- copia, ej. "AV 8484"
  ADD maleta_vuelo_salida DATETIME NULL,        -- copia de actual (o schedule) al registrar
  ADD maleta_vuelo_destino VARCHAR(150) NULL,   -- copia
  ADD maleta_vuelo_manual TINYINT(1) NOT NULL DEFAULT 0,
  ADD maleta_minutos_disponibles INT NULL,      -- salida - registro, en minutos (NULL si es manual)
  ADD KEY idx_maleta_vuelo (maleta_vuelo_id);
```
Hay que actualizar `Administracion_Model_DbTable_Maletas::insert()` y `update()`, que arman el SQL columna por columna. **Conservar** la lógica que lee `over` y `step` de la sesión.

### 4.5 Tabla nueva `alertas_vuelo`
`alerta_id`, `alerta_vuelo_id`, `alerta_destino ENUM('normal','over')`, `alerta_fecha DATETIME`, `alerta_maletas TEXT` (códigos enviados), `alerta_resultado VARCHAR(255)`, con `UNIQUE (alerta_vuelo_id, alerta_destino)`. Esta tabla garantiza que se envíe **una sola alerta por vuelo y destino**.

### 4.6 `info_pagina`: configuración
```sql
ALTER TABLE info_pagina
  ADD info_pagina_alerta_minutos INT NOT NULL DEFAULT 60,
  ADD info_pagina_cron_token VARCHAR(64) NULL;
```
Agregar `info_pagina_alerta_minutos` al formulario de `informacionController` (admin). El token del cron puede ir aquí o en `config.php`; si va en `info_pagina`, **no mostrarlo** en el formulario.

## 5. Sincronización y alertas (cron)

**Endpoint nuevo:** `app/modules/page/Controllers/cronController.php` → `/page/cron/run?token=XXX`. Va en el módulo `page` para no exigir sesión de administrador.

1. Valida el token con `hash_equals`. Si no coincide, responde 403.
2. Toma un lock con `SELECT GET_LOCK('opain_cron', 0)`. Si no lo consigue, termina; así se evitan dos ejecuciones al mismo tiempo.
3. Ejecuta `Core_Model_Vuelos::sincronizar()`:
   - curl con Basic Auth, timeout de 20 s y `CURLOPT_FAILONERROR`;
   - `simplexml_load_string` con `LIBXML_NOCDATA`, filtrado y upsert en una transacción, y limpieza de vuelos viejos;
   - actualiza `vuelos_sync`.
   - **Si falla, no borra nada**: se conservan los últimos datos buenos y se registra el error.
4. Ejecuta `Core_Model_Alertas::procesar()` (§7).
5. Libera el lock y responde un JSON corto `{sync: ok|error, filas, alertas}` para el historial de cron-job.org.

**Configurar en cron-job.org:** la URL de producción con el token, cada 1 minuto. El cliente lo configura; déjalo documentado.

**Alternativa sin cron:** el endpoint de vuelos (§6.2) revisa `vuelos_sync.sync_ultimo_ok`. Si tiene más de 3 minutos, ejecuta `sincronizar()` (con el mismo lock) antes de responder. Las alertas **solo** corren en el cron.

**Modelos nuevos** (seguir el estilo de `app/modules/administracion/Models/DbTable/*`, que extienden `Db_Table`):
- `Administracion_Model_DbTable_Vuelos` (`vuelos`)
- `Administracion_Model_DbTable_Vuelossync`
- `Administracion_Model_DbTable_Alertasvuelo`
- `Core_Model_Vuelos` (lógica de sincronización y consulta)
- `Core_Model_Alertas`

## 6. Registro por lotes

### 6.1 Backend (`maletasController`)

- **Refactor:** pasar el cuerpo de `storeAction` a `private function registrarMaleta(array $input): array`, que devuelve `['status'=>'ok'|'error', 'mensaje'=>..., 'maleta_id'=>...]`. `storeAction` (el formulario viejo) queda como un envoltorio delgado para no romper nada.
- **Acción nueva `storeAjaxAction`:** recibe **una** maleta (`maleta_codigo`, `maleta_aerolinea`, `connection_select`, `connection`, `vuelo_id` o `vuelo_manual`) y responde JSON. Debe:
  - validar el nivel (1, 2 o 3) y el CSRF;
  - revisar que la maleta no exista (respuesta `ext`);
  - si viene `vuelo_id`, revisar **en el servidor** que el vuelo exista, que su `vuelo_linecode` sea igual al `aerolinea_iata` de la aerolínea efectiva (la de conexión o la de la etiqueta) y que no tenga status DEP/CAN;
  - guardar las copias del vuelo y `maleta_minutos_disponibles` (calculado con `COALESCE(actual, schedule)` menos la hora de registro);
  - enviar las notificaciones actuales (correo y Dorabot) **igual que hoy**;
  - devolver el resultado.
  - El filtro nacional/internacional **no** se valida en el servidor, porque existe "Ver todos".
- **Por qué una petición por maleta:** cada registro tarda entre 5 y 8 s, porque el correo y el WhatsApp se envían de forma síncrona (medido en local). Si el lote fuera en una sola petición, 10 maletas pasarían el timeout de PHP. El frontend envía las maletas **una tras otra** y muestra el progreso. Para el usuario sigue siendo un solo clic en "Registrar todas".
- Extender `getAirlineAction` para que también devuelva `airline_iata`.

### 6.2 Endpoint de vuelos

`/administracion/maletas/vuelos?aerolinea=XXX&rango=hoy|24h&todos=0|1&q=texto`

- `aerolinea`: código de 3 dígitos de la aerolínea efectiva. El servidor busca su `aerolinea_iata`.
- Filtros: `vuelo_linecode = iata`, status distinto de DEP/CAN y (`vuelo_schedule >= NOW()` o `vuelo_actual >= NOW()`).
- `rango=hoy` → salida `<= HOY 23:59:59`; `24h` → `<= NOW() + 24h`.
- `todos=0` → aplica `vuelo_tipo` según la sesión: sin `over` → `D`; `over=1` con `step=N` → `D`; `over=1` con `step=I` → `I`; `over=2` → `I`; `over=3` → `D`. Con `todos=1` no filtra por tipo.
- `q`: filtra por número de vuelo (`vuelo_numero LIKE 'q%'`). También se acepta "AV8484" o "AV 8484".
- Orden: `COALESCE(vuelo_actual, vuelo_schedule) ASC`.
- Respuesta: `vuelo_id`, `numero` ("AV 8484"), `destino`, `schedule` (HH:mm), `actual` (HH:mm, si es distinta), `status_texto` y `puerta`. Usar las mismas consultas en el servidor para la validación de §6.1.

### 6.3 Frontend (`Views/maletas/registrar.php` + JS)

Mantener el diseño actual de la página (header negro y amarillo, fondo `dorado-01.jpg`, botones existentes). El formulario de una maleta se cambia por:

1. **El campo de escaneo queda siempre activo.** Al completar 10 dígitos (pistola, teclado o cámara) se llama a `getAirline`:
   - si es válida, se agrega una fila a la tabla del lote, se limpia el campo y vuelve el foco para el siguiente escaneo;
   - si la maleta ya está registrada (`ext`), no existe la aerolínea o la maleta ya está en el lote, se muestra el aviso en la misma línea, sin `alert()`.
2. **Cada fila de la tabla del lote tiene:**
   - casilla de selección;
   - código;
   - aerolínea;
   - "¿Conexión?" (SI/NO + select de aerolínea; al cambiar, se recarga la lista de vuelos de esa fila);
   - selector de vuelo con buscador, mostrando "AV 8484 · Santa Marta · 07:05 (act. 07:32) · Puerta D13";
   - opción "Vuelo no listado" que abre un campo de texto;
   - botón para quitar la fila.
3. **Encima de la tabla:**
   - interruptores "Hoy / Próximas 24 h" y "Ver todos los vuelos";
   - botón **"Asignar vuelo a seleccionadas"**: solo se habilita si todas las filas seleccionadas tienen la misma aerolínea efectiva, y abre el selector de vuelo una vez para todas.
4. **Botón "Registrar todas (N)":** se habilita cuando todas las filas tienen un vuelo o un vuelo manual. Envía las maletas una por una a `storeAjax`; cada fila muestra ⏳, ✓ o ✗ con el motivo. Al terminar muestra un resumen; las filas registradas desaparecen y las fallidas quedan para reintentar.
5. **Cámara (Quagga, en `public/skins/administracion/js/main.js`):** hoy se detiene al leer el primer código. Debe quedar en **modo continuo**: cada lectura agrega una fila; se ignora el mismo código si se repite en menos de 3 s; hay una señal visual o sonora; se detiene al cerrar el modal.
6. Para no perder el lote si se recarga la página por error, guardarlo en `sessionStorage` (con try/catch).

## 7. Alertas de salida próxima (`Core_Model_Alertas::procesar()`)

1. `minutos = info_pagina_alerta_minutos`.
2. Buscar los vuelos cuya salida (`COALESCE(vuelo_actual, vuelo_schedule)`) esté entre `NOW()` y `NOW() + minutos`, con status distinto de DEP/CAN.
3. Para cada vuelo, buscar las maletas con `maleta_vuelo_id = vuelo_id` y `maleta_estado = ''`, y agruparlas por destino: `normal` (maleta sin `maleta_over_tipo`) u `over`.
4. Si no existe una fila en `alertas_vuelo` para (vuelo, destino), enviar la alerta y registrarla. Si el `INSERT` falla por la clave única, no enviar (eso protege contra condiciones de carrera).
5. **Correo:** método nuevo `Core_Model_Sendingemail::notificarAlertaVuelo($aerolinea, $vuelo, $maletas, $esOver)` con la plantilla nueva `app/modules/core/Views/templatesemail/notificarAlertaVuelo.php`. Debe incluir: vuelo, destino, salida programada y actualizada, minutos restantes y una tabla de maletas (código, hora de registro, tipo over). Destinatarios: `aerolinea_correo` o `aerolinea_correo_over`, separados por coma.
   - **Importante:** hoy `notificarRegistro` y `notificarReclamo` deciden el destinatario con `Session::get('over')`, pero el cron no tiene sesión. El método nuevo recibe `$esOver` explícito.
   - En development, `Core_Model_Mail::sed()` ya redirige todo a `desarrollo8@omegawebsystems.com`.
6. **WhatsApp:** mover `notificarDorabot`, el token y `$plantillasDorabot` de `maletasController` a un modelo nuevo `Core_Model_Dorabot`, para que el cron también lo use. El token y la configuración **siguen estando en un solo lugar**: el modelo. El controlador queda llamando al modelo.
   - Agregar el evento `'alerta' => ['normal' => '', '1' => '', '2' => '', '3' => '']`. Si la plantilla está vacía, **no se envía** y no se registra error. Así queda listo para cuando exista la plantilla.
   - En development, ya se redirige al 3142054891.
7. La alerta se calcula con la aerolínea efectiva de la maleta (la de conexión si `maleta_conexion = 1`), igual que las notificaciones actuales.

## 8. Pantallas públicas (módulo `page`)

Archivos: `app/modules/page/Views/index/noreclamadas*.php`, `public/skins/page/js/main.js` y `getNoReclamadas*Action` en `app/modules/page/Controllers/indexController.php`.

- **Backend:** agregar a cada fila del JSON:
  - `vuelo` (número, o "Manual: XXX");
  - `salida` (HH:mm de la salida actualizada);
  - `tiempo_restante` (texto: "45 min", "1 h 10 min", "Salió" o "—" si es manual);
  - `urgencia`: `alta` si falta menos que `info_pagina_alerta_minutos`, `media` si falta menos del doble, `ninguna` en otro caso, y `salio` si ya pasó la salida.
  - Para la hora usar un `LEFT JOIN vuelos ON vuelo_id = maleta_vuelo_id` y tomar `COALESCE(vuelo_actual, vuelo_schedule, maleta_vuelo_salida)`.
  - Las 4 acciones repiten casi el mismo código. Se puede sacar un helper privado, pero sin cambiar los filtros actuales (incluida la regla de seguir mostrando las reclamadas durante 5 minutos).
- **Vista y JS:** agregar las columnas Vuelo, Salida y Tiempo restante (ajustar el `colspan` de los encabezados). Resaltar la fila según la urgencia con las clases de Bootstrap que ya se usan (`table-danger`, `table-warning`).
- **Quitar los degradados** de los encabezados de las pantallas over (`noreclamadasoveruno.php:19`, `noreclamadasoverdos.php:19`, `noreclamadasovertres.php:19`; son `linear-gradient` morado, rosado y cian). Reemplazarlos por un color **sólido** de la paleta actual del sitio (negro y amarillo `#ffd04f` de El Dorado, como el header del admin), y quitar también el `rgba` translúcido de la etiqueta "TIPO N". Nada de degradados, sombras ni efectos nuevos.
- **Bug encontrado en un cambio sin commit de `app/layout/page/page.php`:** `filemtime(APP_PATH . '/skins/page/css/global.css')` apunta a `app/skins/...`, que no existe. Por eso la versión sale vacía (`global.css?v=`) y el cache-busting no funciona. Corregirlo a `APP_PATH . '../public/skins/...'` en las 3 líneas, o confirmar con el usuario si está a medias.

## 9. Listado del admin (`/administracion/maletas`)

- `Views/maletas/index.php` y `indexAction`: agregar las columnas **Vuelo**, **Salida** (actualizada, con la programada en un tooltip si es distinta), **Tiempo restante** (solo si está sin reclamar) y **Margen al registrar** (`maleta_minutos_disponibles`, ej. "40 min").
- Resaltar con el mismo criterio de urgencia de §8.
- Agregar el filtro "Vuelo" (texto, `maleta_vuelo_numero LIKE`) a los filtros que ya están en sesión (`filters()` / `getFilter()`).
- Para la salida actualizada, obtener de una sola vez los `vuelos` de la página (un `IN (...)` con los `maleta_vuelo_id`), sin hacer una consulta por fila.
- Respetar las reglas de visibilidad por nivel que ya existen (nivel 3 solo over, nivel 2 según `user_puedeover`, nivel 4 solo su aerolínea).
- `manageAction` (editar): mostrar el vuelo como solo lectura; no hace falta poder editarlo.

## 10. Reclamo por lotes

- **Refactor:** pasar el cuerpo de `reclamoUpdateAction` a `private function reclamarMaleta($codigo): array`. Acción nueva `reclamoAjaxAction` (una maleta, JSON, CSRF, nivel 1, 2 o 3). `reclamoUpdateAction` queda como envoltorio.
- **Vista `reclamar.php`:** el mismo patrón de lote que en el registro, pero sin selección de vuelo. Al escanear, se consulta `getBag`, que hay que ampliar para devolver también la aerolínea, el vuelo, la salida y el tipo over. Cada fila muestra código, aerolínea, vuelo y salida (solo lectura) y su estado:
  - "Sin reclamar": se puede reclamar;
  - "Ya reclamada" o "No encontrada": se marca en rojo y queda excluida del envío.
- Botón **"Reclamar todas (N)"**, enviando una por una y mostrando el progreso, como en el registro.
- Las notificaciones son las mismas de hoy, una por maleta.

## 11. Orden de implementación sugerido

1. SQL (§4) y modelos DbTable. Campo IATA en el admin de aerolíneas (`aerolineasController` getData, la vista `manage.php` y el modelo `Aerolineas` insert/update).
2. `Core_Model_Vuelos::sincronizar()` y `cronController`. Probar contra el XML real y contra el archivo de ejemplo [eclipsx.xml](eclipsx.xml).
3. Endpoint de vuelos (§6.2).
4. Refactor de registro y reclamo en funciones reutilizables y acciones AJAX.
5. Frontend de registro por lotes y modo continuo de la cámara.
6. Frontend de reclamo por lotes.
7. Mover Dorabot a `Core_Model_Dorabot` y crear las alertas (§7).
8. Pantallas públicas (§8) y listado del admin (§9).
9. Configuración (`info_pagina_alerta_minutos`) en el admin de información.

## 12. Pruebas y criterios de aceptación

Probar en local (`php -S 0.0.0.0:8043` desde `public/`, base `opain_maletas`). Para usar el admin hace falta sesión con OTP; en una prueba automatizada se puede crear una sesión a mano en `/opt/homebrew/var/php/sessions`, junto con la cookie `consentimiento=aceptado`, sin la cual `bootstrap.php` no arranca la sesión. En development, todos los correos y WhatsApp van al equipo de desarrollo; no hace falta tocar destinatarios. **Borrar los datos de prueba al terminar.**

- [ ] La sincronización guarda solo filas D/TU/S. Al correrla dos veces no duplica (`vuelo_id` se mantiene). Si el XML falla (URL o credencial mala), los datos anteriores se conservan y queda el error en `vuelos_sync`.
- [ ] `/page/cron/run` sin token o con token incorrecto responde 403. Dos llamadas simultáneas no se ejecutan dos veces.
- [ ] Una maleta de Avianca (134) en sesión normal muestra vuelos AV nacionales de hoy que no han salido. "Próximas 24 h" agrega los de la madrugada siguiente. "Ver todos" agrega los internacionales.
- [ ] Una maleta de Iberia (075) muestra el IB 6946 (codeshare).
- [ ] Con over 2, solo aparecen vuelos internacionales por defecto.
- [ ] Un vuelo demorado (schedule pasado, actual futuro, sin DEP) aparece. Un vuelo con DEP o CAN no aparece.
- [ ] Una maleta de conexión muestra los vuelos de la aerolínea de conexión.
- [ ] Se registra un lote de 5 maletas de 2 aerolíneas, con "Asignar vuelo a seleccionadas", una con "Vuelo no listado", una duplicada (debe rechazarse) y una con aerolínea sin IATA. Cada maleta queda con sus datos de vuelo y `maleta_minutos_disponibles` correctos, y se envía 1 correo + WhatsApp por maleta, como hoy.
- [ ] Si se manda un `vuelo_id` de otra aerolínea modificando la petición a mano, el servidor lo rechaza.
- [ ] La cámara lee varias etiquetas seguidas sin cerrarse.
- [ ] Se reclama un lote de 3 maletas (una ya reclamada debe quedar excluida). El estado, la fecha de salida, `log_maletas` y las notificaciones quedan bien.
- [ ] Alerta: con `alerta_minutos=60` y un vuelo a 50 min con 2 maletas pendientes (una normal y una over), llegan 2 correos (destino normal y over) que listan sus maletas. En la siguiente ejecución del cron no se reenvían. Sin plantilla de Dorabot no se envía WhatsApp y no queda error.
- [ ] Las 4 pantallas públicas cargan, se refrescan solas y muestran vuelo, salida y tiempo restante con resaltado. Las maletas antiguas sin vuelo muestran "—". Las pantallas over ya no tienen degradados.
- [ ] El listado del admin muestra las columnas nuevas, el filtro por vuelo funciona y las reglas de visibilidad por nivel siguen iguales.
- [ ] El flujo `/page/index/reclamos` (nivel 3, módulo `page`) sigue reclamando una maleta como antes.
- [ ] Las maletas registradas antes de este cambio (sin vuelo) se siguen viendo y se pueden reclamar sin errores.

## 13. Pendientes con el cliente (no bloquean el desarrollo)

- Completar el IATA de: 035 Latam, 544 Latam, 999 NN Avianca, 067 Turpial. Confirmar que el 333 es Viva (VB).
- Crear en Botmaker la plantilla de WhatsApp para la alerta de salida y pasar su nombre (se agrega en `Core_Model_Dorabot::$plantillasDorabot['alerta']`).
- Configurar en cron-job.org la URL `/page/cron/run?token=...` cada minuto (en producción).
