#!/bin/bash
total_global_funcs=0
total_global_lines=0
excluded_files=""

# Contar funciones PHP excluyendo nombres que contengan 'old'/'OLD'
count_php_functions() {
  local file="$1"
  # Cuenta definiciones de métodos en una sola línea, incluyendo 'static',
  # pero excluye cualquier nombre que contenga 'old' en cualquier posición.
  # Ej: public function buscarProveedoresOLDAction() { ... }
  grep -E "^\s*(public|protected|private)\s+(static\s+)?function\s+" "$file" 2>/dev/null \
    | grep -Eiv "^\s*(public|protected|private)\s+(static\s+)?function\s+[A-Za-z0-9_]*old[A-Za-z0-9_]*\s*\(" \
    | wc -l \
    | awk '{print $1}'
}

# Función para verificar si excluir
should_exclude() {
  local file="$1"
  if [[ "$file" =~ (old|OLD|copy|Copy) ]]; then
    excluded_files="$excluded_files\n  - $file"
    return 0
  fi
  return 1
}

# Módulos existentes, incluyendo core
for module in page administracion core; do
  echo "=== Módulo: $module ==="
  
  # Views
  echo "Views:"
  total_views_funcs=0
  total_views_lines=0
  for view_file in $(find $module/Views -name "*.php" 2>/dev/null); do
    if should_exclude "$view_file"; then continue; fi
    funcs=0  # Views no tienen funciones
    lines=$(wc -l < "$view_file" | awk '{print $1}')
    relative_path=${view_file#$module/Views/}
    echo "  - $relative_path: Líneas: $lines"
    total_views_lines=$((total_views_lines + lines))
  done
  echo "  Total Views: Funciones: $total_views_funcs, Líneas: $total_views_lines"
  
  # Controllers
  echo "Controllers:"
  total_ctrl_funcs=0
  total_ctrl_lines=0
  for ctrl_file in $(find $module/Controllers -name "*Controller.php" 2>/dev/null); do
    if should_exclude "$ctrl_file"; then continue; fi
    funcs=$(count_php_functions "$ctrl_file")
    lines=$(wc -l < "$ctrl_file" | awk '{print $1}')
    relative_path=${ctrl_file#$module/Controllers/}
    echo "  - $relative_path: Funciones: $funcs, Líneas: $lines"
    total_ctrl_funcs=$((total_ctrl_funcs + funcs))
    total_ctrl_lines=$((total_ctrl_lines + lines))
  done
  echo "  Total Controllers: Funciones: $total_ctrl_funcs, Líneas: $total_ctrl_lines"
  
  # Models (incluyendo DbTable y Upload si existen)
  echo "Models:"
  total_model_funcs=0
  total_model_lines=0
  for model_file in $(find $module/Models -name "*.php" 2>/dev/null); do
    if should_exclude "$model_file"; then continue; fi
    funcs=$(count_php_functions "$model_file")
    lines=$(wc -l < "$model_file" | awk '{print $1}')
    relative_path=${model_file#$module/Models/}
    echo "  - $relative_path: Funciones: $funcs, Líneas: $lines"
    total_model_funcs=$((total_model_funcs + funcs))
    total_model_lines=$((total_model_lines + lines))
  done
  echo "  Total Models: Funciones: $total_model_funcs, Líneas: $total_model_lines"
  
  # Total por módulo
  module_funcs=$((total_ctrl_funcs + total_model_funcs))
  module_lines=$((total_ctrl_lines + total_model_lines + total_views_lines))
  echo "  Total Módulo $module: Funciones: $module_funcs, Líneas: $module_lines"
  
  # Acumular global
  total_global_funcs=$((total_global_funcs + module_funcs))
  total_global_lines=$((total_global_lines + module_lines))
  
  echo ""
done

# Bootstrap
echo "=== Bootstrap ==="
funcs=0
lines=$(wc -l < "../bootstrap.php" | awk '{print $1}')
if ! should_exclude "../bootstrap.php"; then
  echo "  - bootstrap.php: Funciones: $funcs, Líneas: $lines"
  echo "  Total Bootstrap: Funciones: $funcs, Líneas: $lines"
  total_global_funcs=$((total_global_funcs + funcs))
  total_global_lines=$((total_global_lines + lines))
fi

# Layout
echo "=== Layout ==="
total_layout_funcs=0
total_layout_lines=0
for layout_file in $(find ../layout -name "*.php" 2>/dev/null); do
  if should_exclude "$layout_file"; then continue; fi
  funcs=$(count_php_functions "$layout_file")
  lines=$(wc -l < "$layout_file" | awk '{print $1}')
  relative_path=${layout_file#../layout/}
  echo "  - $relative_path: Funciones: $funcs, Líneas: $lines"
  total_layout_funcs=$((total_layout_funcs + funcs))
  total_layout_lines=$((total_layout_lines + lines))
done
echo "  Total Layout: Funciones: $total_layout_funcs, Líneas: $total_layout_lines"
total_global_funcs=$((total_global_funcs + total_layout_funcs))
total_global_lines=$((total_global_lines + total_layout_lines))

# Config
echo "=== Config ==="
total_config_funcs=0
total_config_lines=0
for config_file in $(find ../config -name "*.php" 2>/dev/null); do
  if should_exclude "$config_file"; then continue; fi
  funcs=$(count_php_functions "$config_file")
  lines=$(wc -l < "$config_file" | awk '{print $1}')
  relative_path=${config_file#../config/}
  echo "  - $relative_path: Funciones: $funcs, Líneas: $lines"
  total_config_funcs=$((total_config_funcs + funcs))
  total_config_lines=$((total_config_lines + lines))
done
echo "  Total Config: Funciones: $total_config_funcs, Líneas: $total_config_lines"
total_global_funcs=$((total_global_funcs + total_config_funcs))
total_global_lines=$((total_global_lines + total_config_lines))

# Public Page (JS/CSS)
echo "=== Public Page (JS/CSS) ==="
total_public_page_lines=0
for asset_file in $(find ../../public/skins/page -name "*.js" -o -name "*.css" 2>/dev/null); do
  if should_exclude "$asset_file"; then continue; fi
  lines=$(wc -l < "$asset_file" | awk '{print $1}')
  relative_path=${asset_file#../../public/skins/page/}
  echo "  - $relative_path: Líneas: $lines"
  total_public_page_lines=$((total_public_page_lines + lines))
done
echo "  Total Public Page: Funciones: 0, Líneas: $total_public_page_lines"
total_global_lines=$((total_global_lines + total_public_page_lines))

# Public Admin (JS/CSS)
echo "=== Public Admin (JS/CSS) ==="
total_public_admin_lines=0
for asset_file in $(find ../../public/skins/administracion -name "*.js" -o -name "*.css" 2>/dev/null); do
  if should_exclude "$asset_file"; then continue; fi
  lines=$(wc -l < "$asset_file" | awk '{print $1}')
  relative_path=${asset_file#../../public/skins/administracion/}
  echo "  - $relative_path: Líneas: $lines"
  total_public_admin_lines=$((total_public_admin_lines + lines))
done
echo "  Total Public Admin: Funciones: 0, Líneas: $total_public_admin_lines"
total_global_lines=$((total_global_lines + total_public_admin_lines))

# Total global
echo "=== Total General ==="
echo "Funciones: $total_global_funcs, Líneas: $total_global_lines"

# Archivos excluidos
if [ -n "$excluded_files" ]; then
  echo ""
  echo "=== Archivos Excluidos (contienen 'old', 'OLD', 'copy', etc.) ==="
  echo -e "$excluded_files"
fi