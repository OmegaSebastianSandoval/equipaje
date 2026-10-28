<?php

/**
 * Modelo del modulo Core que envia las notificaciones de WhatsApp por Dorabot (Botmaker).
 * El token y las plantillas viven solo aqui; los controladores y el cron llaman a este modelo.
 */
class Core_Model_Dorabot
{
  private $dorabotUrl = 'https://api.botmaker.com/v2.0/chats-actions/trigger-intent';
  private $dorabotCanal = 'dorabot-whatsapp-573228220300';
  private $dorabotToken = 'eyJhbGciOiJIUzUxMiJ9.eyJidXNpbmVzc0lkIjoiZG9yYWJvdCIsIm5hbWUiOiJBbGVqYW5kcmEgR2lyYWxkbyIsImFwaSI6dHJ1ZSwiaWQiOiJFNllSTzIxN3JhZERGQWZXVTRwc3pPeWRiVDUyIiwiZXhwIjoxODY1NjI0MjYzLCJqdGkiOiJFNllSTzIxN3JhZERGQWZXVTRwc3pPeWRiVDUyIn0.hHfUFmldSMQzuKYErjM6qjad0M22wzQtHRizq924KC2IuJxla6b62Gc2gH2ta57w4lE-SsoYyoSrDVZU9ixsWA';

  /**
   * Plantillas de Dorabot por evento.
   * 'normal' es para maletas sin over, '1', '2' y '3' para cada tipo de over.
   * Si la plantilla esta vacia no se envia el mensaje (la alerta queda lista para cuando exista en Botmaker).
   */
  /**
   * Envios hechos en este request (solo se usa en development).
   */
  private static $enviadosDev = [];

  private $plantillasDorabot = [
    'registro' => [
      'normal' => 'notificaciones_dorabot_nivel_5v2',
      '1' => 'sobredimensionado_contingencia2',
      '2' => 'sobredimensionado_inter1',
      '3' => 'sobredimensionado_nacional4',
    ],
    'reclamo' => [
      'normal' => 'notificaciones_dorabot_nivel5_3v3',
      '1' => 'equipaje_sobredimensionado_over1',
      '2' => 'sobredimension_comodin',
      '3' => 'equipaje_sobredimensionado_over3',
    ],
    'alerta' => [
      'normal' => '',
      '1' => '',
      '2' => '',
      '3' => '',
    ],
  ];

  /**
   * Envia una notificacion de WhatsApp por Dorabot (Botmaker).
   * La plantilla se toma de $plantillasDorabot segun el evento y el tipo de over de la maleta.
   *
   * @param string $phone telefono de destino (sin el indicativo 57).
   * @param string $evento 'registro', 'reclamo' o 'alerta'.
   * @param string $aerolinea nombre de la aerolinea.
   * @param string $codigoEquipaje codigo de la maleta (o lista de codigos en la alerta).
   * @param string $overTipo tipo de over de la maleta ('1', '2', '3') o vacio si es una maleta normal.
   * @param array $extra variables adicionales para la plantilla.
   * @return void
   */
  public function notificar($phone, $evento, $aerolinea, $codigoEquipaje, $overTipo = '', $extra = [])
  {
    $esOver = in_array($overTipo, ['1', '2', '3']);
    $plantilla = $esOver ? $this->plantillasDorabot[$evento][$overTipo] : $this->plantillasDorabot[$evento]['normal'];
    if ($plantilla == '' || trim($phone) == '') {
      return;
    }

    if (Config_Config::getInstance()->getValue('notificaciones/redirigir')) {
      // Con la redireccion activa todos los telefonos se redirigen a uno solo: se envia una vez por maleta y evento
      $clave = $evento . '|' . $codigoEquipaje . '|' . $overTipo;
      if (isset(self::$enviadosDev[$clave])) {
        return;
      }
      self::$enviadosDev[$clave] = true;
      $phone = '3142054891';
    }

    $variables = [
      "aerolinea" => $aerolinea,
      "codigoEquipaje" => $codigoEquipaje
    ];

    if ($esOver) {
      $variables["overTipo"] = $overTipo;
    }

    if ($evento == 'reclamo' && $overTipo == '2') {
      $variables["comodin"] = "sobredimensionado 2 - Internacional";
    }
    $variables = array_merge($variables, $extra);

    $data = [
      "chat" => [
        "channelId" => $this->dorabotCanal,
        "contactId" => "57" . trim($phone)
      ],
      "intentIdOrName" => $plantilla,
      "variables" => $variables,
      "tags" => [
        "tagName" => true,
        "anotherTag" => false
      ],
      "webhookPayload" => "string"
    ];

    $ch = curl_init($this->dorabotUrl);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
      'Content-Type: application/json',
      'access-token: ' . $this->dorabotToken
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    $error = '';
    if (curl_errno($ch)) {
      $error = curl_error($ch);
    } else if ($httpCode >= 400) {
      $error = 'HTTP ' . $httpCode . ' (' . $plantilla . '): ' . $response;
    }

    if ($error != '') {
      $db = App::getDbConnection()->getConnection();
      $logModel = new Administracion_Model_DbTable_Logdorabot();
      $logData = array(
        "date" => date('Y-m-d'),
        "log" => mysqli_real_escape_string($db, $error),
        "code" => mysqli_real_escape_string($db, $codigoEquipaje)
      );
      $logModel->insert($logData);
    }

    curl_close($ch);
  }
}
