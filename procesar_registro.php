<?php
// Función para responder en JSON
function respond($data, $httpCode = 200) {
    http_response_code($httpCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// Función para obtener y sanitizar datos POST
function get($key) {
    return isset($_POST[$key]) ? trim($_POST[$key]) : '';
}

// Función auxiliar para obtener nombre del evento
function obtenerNombreEvento($codigo_evento) {
    $eventos = [
        'fiesta_neon' => 'Fiesta Neon Underwater',
        'sunset_beach' => 'Sunset Beach Party',
        'retro_80s' => "Retro 80's Night",
        'electronic_festival' => 'Electronic Festival Quito',
        'beach_party' => 'Beach Party Montañita'
    ];
    return isset($eventos[$codigo_evento]) ? $eventos[$codigo_evento] : $codigo_evento;
}

// Verificar si es una solicitud de búsqueda (GET) o registro (POST)
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $ref = isset($_GET['ref']) ? trim($_GET['ref']) : '';
    $format = isset($_GET['format']) ? strtolower($_GET['format']) : '';

    // Buscar el registro (si se proporcionó referencia)
    $found = null;
    $file = __DIR__ . DIRECTORY_SEPARATOR . 'data' . DIRECTORY_SEPARATOR . 'registrations.csv';
    if ($ref !== '' && file_exists($file) && is_readable($file)) {
            if (($fp = fopen($file, 'r')) !== false) {
                $headers = fgetcsv($fp);
                while (($row = fgetcsv($fp)) !== false) {
                    if (isset($row[0]) && strcasecmp($row[0], $ref) === 0) {
                        // Mapear headers a valores de forma segura (soporta filas antiguas con menos columnas)
                        $found = [];
                        foreach ($headers as $i => $h) {
                            $found[$h] = isset($row[$i]) ? $row[$i] : '';
                        }
                        break;
                    }
                }
                fclose($fp);
            }
    }

    // Si se solicita formato JSON (API)
    if ($format === 'json') {
        if ($found) {
            respond(['estado' => 'éxito', 'registro' => $found]);
        } else {
            respond(['estado' => 'error', 'mensaje' => 'Registro no encontrado'], 404);
        }
    }

    // Mostrar la interfaz HTML de búsqueda integrada aquí (sin archivo externo)
    // Usamos la plantilla original de buscar_registro.php embebida para que todo esté en un solo archivo.
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width,initial-scale=1">
        <title>Buscar Registro - FiestaMania Ecuador</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <style>
            /* (estilos reducidos para brevedad - se mantienen los mismos que tenía la plantilla) */
            :root { --primary: #FF5722; --secondary: #FFC107; --accent: #00BCD4; --dark: #1a1a2e; --light: #f8f9fa; --text: #333; --shadow: 0 4px 15px rgba(0, 0, 0, 0.2); --transition: all 0.3s ease; }
            *{margin:0;padding:0;box-sizing:border-box}
            body{font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;line-height:1.6;color:var(--text);min-height:100vh;background:linear-gradient(135deg, rgba(26,26,46,0.9) 0%, rgba(255,87,34,0.15) 50%, rgba(255,193,7,0.1) 100%), url('https://images.unsplash.com/photo-1533174072545-7a4b6ad7a6c3?ixlib=rb-4.0.3&auto=format&fit=crop&w=2070&q=80');background-size:cover;background-position:center;padding:20px}
            .container{max-width:1200px;margin:0 auto}
            .card{background:rgba(255,255,255,0.95);backdrop-filter:blur(10px);border-radius:20px;padding:40px;box-shadow:var(--shadow);margin-bottom:30px;border:1px solid rgba(255,255,255,0.2)}
            .search-form{display:flex;gap:15px;max-width:600px;margin:0 auto 30px}
            .search-input{flex:1;padding:15px 20px;border:2px solid #e0e0e0;border-radius:50px;font-size:1rem;background:white}
            .btn{padding:15px 30px;background:linear-gradient(45deg,var(--primary),var(--secondary));color:white;border:none;border-radius:50px;font-weight:600;cursor:pointer}
            .btn-secondary{background:transparent;border:2px solid var(--primary);color:var(--primary);padding:13px 26px;border-radius:50px}
            .btn-secondary:hover{background:var(--primary);color:white}
            .result-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:20px;margin-top:30px}
            .result-card{background:white;padding:25px;border-radius:15px;box-shadow:0 4px 15px rgba(0,0,0,0.1);border-left:4px solid var(--primary)}
            .field-label{font-size:.9rem;color:#666;margin-bottom:8px;font-weight:600;text-transform:uppercase}
            .field-value{font-size:1.1rem;color:var(--dark);font-weight:600}
            .not-found{ text-align:center;padding:40px;background:linear-gradient(135deg,#ff6b6b,#ee5a24);color:white;border-radius:15px;margin:20px 0 }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="card search-section">
                <h2 class="search-title">Buscar Registro</h2>
                <p class="search-subtitle">Ingresa tu número de referencia para consultar los detalles de tu registro</p>
                <form method="get" action="procesar_registro.php" class="search-form">
                    <input type="text" name="ref" placeholder="Ej: FM-20241201-ABC123" value="<?php echo htmlspecialchars($ref); ?>" class="search-input" required>
                    <button type="submit" class="btn pulse"><i class="fas fa-search"></i> Buscar</button>
                </form>
                <?php if ($ref === ''): ?>
                    <div class="api-info">También puedes consultar registros mediante nuestra API: <code>procesar_registro.php?ref=TU_REFERENCIA&format=json</code></div>
                <?php endif; ?>
            </div>

            <?php if ($ref !== ''): ?>
                <div class="card">
                    <?php if ($found): ?>
                        <h2 style="text-align:center;color:var(--dark)"><i class="fas fa-check-circle" style="color:#4CAF50"></i> Registro Encontrado</h2>

                        <?php if (isset($found['imagen_path']) && !empty($found['imagen_path'])): ?>
                            <div class="image-card full-width">
                                <h3 class="field-label"><i class="fas fa-images"></i> Imagen del Evento</h3>
                                <div class="event-image-container" id="imageContainer">
                                    <img src="<?php echo htmlspecialchars($found['imagen_path']); ?>" alt="Imagen del evento" class="event-image" title="Click para ampliar">
                                    <div class="image-overlay"></div>
                                </div>
                                <small style="display:block;margin-top:10px;color:#666;"><i class="fas fa-search-plus"></i> Click en la imagen para ampliar</small>
                            </div>

                            <!-- Modal para la imagen -->
                            <div class="image-modal" id="imageModal">
                                <span class="modal-close" id="closeModal"><i class="fas fa-times"></i></span>
                                <img src="<?php echo htmlspecialchars($found['imagen_path']); ?>" alt="Imagen del evento" class="modal-content" id="modalImage">
                            </div>

                            <script>
                                const imageContainer = document.getElementById('imageContainer');
                                const imageModal = document.getElementById('imageModal');
                                const modalImage = document.getElementById('modalImage');
                                const closeModal = document.getElementById('closeModal');
                                if (imageContainer) {
                                    imageContainer.onclick = function() {
                                        imageModal.style.display = 'flex';
                                        document.body.style.overflow = 'hidden';
                                        modalImage.style.opacity = '0';
                                        setTimeout(()=>{ modalImage.style.transition='opacity 0.3s ease'; modalImage.style.opacity='1'; },50);
                                    };
                                }
                                if (closeModal) closeModal.onclick = function(){ imageModal.style.display='none'; document.body.style.overflow='auto'; modalImage.style.opacity=''; modalImage.style.transition=''; };
                                if (imageModal) imageModal.onclick = function(e){ if (e.target===imageModal) { imageModal.style.display='none'; document.body.style.overflow='auto'; } };
                                document.addEventListener('keydown', function(e){ if (e.key==='Escape' && imageModal.style.display === 'flex') { imageModal.style.display='none'; document.body.style.overflow='auto'; }});
                            </script>
                        <?php endif; ?>

                        <div class="result-grid">
                            <?php
                                $labels = [
                                    'referencia'=>'Número de Referencia','fecha'=>'Fecha de Registro','nombre'=>'Nombre Completo','email'=>'Correo Electrónico','telefono'=>'Teléfono','edad'=>'Edad','ciudad'=>'Ciudad','evento'=>'Evento','cantidad'=>'Cantidad de Entradas','fecha_evento'=>'Fecha del Evento','tipo_pago'=>'Método de Pago','comentarios'=>'Comentarios','newsletter'=>'Newsletter'
                                ];
                                foreach ($labels as $key=>$label) {
                                    if (isset($found[$key]) && $found[$key] !== '') {
                                        $val = $found[$key];
                                        if ($key === 'newsletter') $val = $val === 'si' ? 'Sí' : 'No';
                                        echo "<div class='result-card'><div class='field-label'>".htmlspecialchars($label)."</div><div class='field-value'>".nl2br(htmlspecialchars($val))."</div></div>";
                                    }
                                }
                            ?>
                        </div>
                        <div style="text-align:center;margin-top:20px; display:flex; gap:12px; justify-content:center; flex-wrap:wrap;">
                            <a class="btn" href="procesar_registro.php?ref=<?php echo urlencode($found['referencia']); ?>">Ver Detalle (misma página)</a>
                            <a class="btn btn-secondary" href="index.html">Volver al Inicio</a>
                        </div>
                    <?php else: ?>
                        <div class="not-found"><i class="fas fa-exclamation-triangle"></i><h3>Registro No Encontrado</h3><p>No se encontró la referencia: <strong><?php echo htmlspecialchars($ref); ?></strong></p></div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </body>
    </html>
    <?php
    exit;
}

// Si es OPTIONS (preflight CORS)
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');
    respond(['estado' => 'ok']);
}

// Si no es POST, error
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respond(['estado' => 'error', 'errores' => ['Método no permitido. Use POST para registrar o GET para buscar.']], 405);
}

// Verificar que se estén enviando datos
if (empty($_POST)) {
    respond(['estado' => 'error', 'errores' => ['No se recibieron datos del formulario.']], 400);
}

// Obtener y validar datos
$nombre = get('nombre');
$email = get('email');
$telefono = get('telefono');
$edad = get('edad');
$ciudad = get('ciudad');
$evento = get('evento');
$cantidad = get('cantidad_entradas');
$fecha_evento = get('fecha_evento');
$tipo_pago = get('tipo_pago');
$comentarios = get('comentarios');
$terminos = isset($_POST['terminos']) ? true : false;
$newsletter = isset($_POST['newsletter']) ? true : false;

// Validaciones
$errors = [];
if ($nombre === '') $errors[] = 'El nombre es requerido.';
if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Email inválido o vacío.';
if ($telefono === '') $errors[] = 'El teléfono es requerido.';
if ($edad === '' || !is_numeric($edad) || (int)$edad < 18 || (int)$edad > 80) $errors[] = 'La edad debe estar entre 18 y 80 años.';
if ($ciudad === '') $errors[] = 'La ciudad es requerida.';
if ($evento === '') $errors[] = 'Seleccione un evento de interés.';
if ($cantidad === '' || !is_numeric($cantidad) || (int)$cantidad < 1 || (int)$cantidad > 10) $errors[] = 'La cantidad de entradas debe estar entre 1 y 10.';
if (!$terminos) $errors[] = 'Debe aceptar los términos y condiciones.';

if (!empty($errors)) {
    respond(['estado' => 'error', 'errores' => $errors]);
}

// Generar número de referencia
$numero_referencia = strtoupper(substr(sha1(uniqid('', true)), 0, 10));

// Manejo de archivo subido (opcional)
$imagen_path = '';
if (isset($_FILES['event_image']) && isset($_FILES['event_image']['error']) && $_FILES['event_image']['error'] !== UPLOAD_ERR_NO_FILE) {
    if ($_FILES['event_image']['error'] === UPLOAD_ERR_OK) {
        $tmp = $_FILES['event_image']['tmp_name'];
        $maxSize = 5 * 1024 * 1024; // 5MB
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $tmp);
        finfo_close($finfo);
        $allowed = ['image/jpeg'=>'jpg','image/png'=>'png','image/gif'=>'gif'];
        if (isset($allowed[$mime]) && $_FILES['event_image']['size'] <= $maxSize) {
            $uploadDir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'eventos';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $ext = $allowed[$mime];
            $newName = $numero_referencia . '_' . time() . '.' . $ext;
            $dest = $uploadDir . DIRECTORY_SEPARATOR . $newName;
            if (move_uploaded_file($tmp, $dest)) {
                // ruta relativa para uso en HTML
                $imagen_path = 'uploads/eventos/' . $newName;
            }
        } else {
            // imagen inválida o demasiado grande -> ignorar sin bloquear registro
            $imagen_path = '';
        }
    }
}

// Preparar carpeta y archivo
$dataDir = __DIR__ . DIRECTORY_SEPARATOR . 'data';
if (!is_dir($dataDir)) {
    mkdir($dataDir, 0755, true);
}
$file = $dataDir . DIRECTORY_SEPARATOR . 'registrations.csv';
$isNew = !file_exists($file);

// Si el archivo ya existe pero no tiene la columna imagen_path en la cabecera,
// reescribimos el CSV añadiendo la nueva cabecera y una columna vacía para filas antiguas.
if (!$isNew) {
    $fp_check = @fopen($file, 'r');
    if ($fp_check !== false) {
        $existing_headers = fgetcsv($fp_check);
        fclose($fp_check);
        if ($existing_headers !== false && !in_array('imagen_path', $existing_headers)) {
            // Leer todas las filas existentes
            $all = [];
            if (($fr = fopen($file, 'r')) !== false) {
                $hdr = fgetcsv($fr); // header
                while (($r = fgetcsv($fr)) !== false) {
                    // garantizar que la fila tenga una columna adicional vacía
                    $r[] = '';
                    $all[] = $r;
                }
                fclose($fr);
            }

            // Reescribir con nueva cabecera
            $newHeaders = $existing_headers;
            $newHeaders[] = 'imagen_path';
            if (($fw = fopen($file, 'w')) !== false) {
                if (flock($fw, LOCK_EX)) {
                    fputcsv($fw, $newHeaders);
                    foreach ($all as $rowOld) {
                        fputcsv($fw, $rowOld);
                    }
                    fflush($fw);
                    flock($fw, LOCK_UN);
                }
                fclose($fw);
            }
        }
    }
}

// Registrar en CSV
$fp = @fopen($file, 'a');
if ($fp === false) {
    respond(['estado' => 'error', 'errores' => ['No se pudo abrir el archivo para guardar. Compruebe permisos.']]);
}

// Cabecera si es nuevo
if ($isNew) {
    if (flock($fp, LOCK_EX)) {
        fputcsv($fp, ['referencia','fecha','nombre','email','telefono','edad','ciudad','evento','cantidad','fecha_evento','tipo_pago','comentarios','newsletter','imagen_path']);
        fflush($fp);
        flock($fp, LOCK_UN);
    }
}

// Sanitizar campos
$safe = function($value) {
    $value = str_replace(["\r", "\n"], ' ', $value);
    return mb_substr($value, 0, 1000);
};

// Preparar fila
$row = [
    $numero_referencia,
    date('Y-m-d H:i:s'),
    $safe($nombre),
    $safe($email),
    $safe($telefono),
    $safe($edad),
    $safe($ciudad),
    $safe($evento),
    $safe($cantidad),
    $safe($fecha_evento),
    $safe($tipo_pago),
    $safe($comentarios),
    $newsletter ? 'si' : 'no',
    // imagen_path será agregado si se subió una imagen (o vacío)
    isset($imagen_path) ? $safe($imagen_path) : ''
];

// Escribir con bloqueo
if (flock($fp, LOCK_EX)) {
    fputcsv($fp, $row);
    fflush($fp);
    flock($fp, LOCK_UN);
}
fclose($fp);

// Preparar respuesta
$datos_registro = [
    'referencia' => $numero_referencia,
    'fecha_registro' => date('Y-m-d H:i:s'),
    'nombre' => $nombre,
    'email' => $email,
    'telefono' => $telefono,
    'edad' => (int)$edad,
    'ciudad' => $ciudad,
    'evento_codigo' => $evento,
    'evento_nombre' => obtenerNombreEvento($evento),
    'cantidad_entradas' => (int)$cantidad,
    'fecha_evento' => $fecha_evento,
    'tipo_pago' => $tipo_pago,
    'comentarios' => $comentarios,
    'newsletter' => $newsletter ? 'si' : 'no',
    'ip' => $_SERVER['REMOTE_ADDR'] ?? ''
];

// Enviar respuesta exitosa
respond([
    'estado' => 'éxito',
    'mensaje' => '¡Registro completado exitosamente!',
    'numero_referencia' => $numero_referencia,
    'datos' => $datos_registro
]);