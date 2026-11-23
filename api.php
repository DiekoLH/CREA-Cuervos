<?php
require_once "conexion.php";

$action = $_GET['action'] ?? ($_POST['action'] ?? '');

function uploadFile($fieldName, $subFolder, $allowedExt) {
    if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    $origName = basename($_FILES[$fieldName]['name']);
    $ext = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
    if (!in_array($ext, $allowedExt)) {
        return ["error" => "Extensión no permitida: ." . $ext];
    }
    $dir = __DIR__ . "/uploads/" . $subFolder . "/";
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    $safeName = time() . "_" . preg_replace("/[^a-zA-Z0-9._-]/", "_", $origName);
    $destPath = $dir . $safeName;
    if (move_uploaded_file($_FILES[$fieldName]['tmp_name'], $destPath)) {
        return "uploads/" . $subFolder . "/" . $safeName;
    }
    return ["error" => "No se pudo guardar el archivo en el servidor."];
}

switch ($action) {
    case "registro_alumno":
        $nombre = trim($_POST['nombre'] ?? '');
        $matricula = trim($_POST['matricula'] ?? '');
        $carrera = trim($_POST['carrera'] ?? '');
        $correo = trim($_POST['correo'] ?? '');
        $grupo = trim($_POST['grupo'] ?? '');
        $contrasena = $_POST['contrasena'] ?? '';
        $rol = "Estudiante";

        $stmt = $conn->prepare("INSERT INTO datosEstudiante (nombre, matricula, carrera, correo, grupo, contrasena, rol) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $nombre, $matricula, $carrera, $correo, $grupo, $contrasena, $rol);
        if ($stmt->execute()) {
            echo json_encode([
                "ok" => true,
                "user" => [
                    "rol" => $rol,
                    "nombre" => $nombre,
                    "matricula" => $matricula,
                    "carrera" => $carrera,
                    "correo" => $correo,
                    "grupo" => $grupo
                ]
            ]);
        } else {
            echo json_encode(["ok" => false, "error" => "La matrícula o correo ya existe: " . $stmt->error]);
        }
        break;

    case "login_alumno":
        $identificador = trim($_POST['identificador'] ?? '');
        $contrasena = $_POST['contrasena'] ?? '';
        $carrera = trim($_POST['carrera'] ?? '');
        $grupo = trim($_POST['grupo'] ?? '');

        $stmt = $conn->prepare("SELECT id, nombre, matricula, carrera, correo, grupo, contrasena, rol FROM datosEstudiante WHERE (matricula = ? OR correo = ?) AND contrasena = ? LIMIT 1");
        $stmt->bind_param("sss", $identificador, $identificador, $contrasena);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            $carreraFinal = $carrera !== "" ? $carrera : $row['carrera'];
            $grupoFinal = $grupo !== "" ? $grupo : $row['grupo'];
            $rolAsignado = "Estudiante";

            $upd = $conn->prepare("UPDATE datosEstudiante SET carrera = ?, grupo = ?, rol = ? WHERE id = ?");
            $upd->bind_param("sssi", $carreraFinal, $grupoFinal, $rolAsignado, $row['id']);
            $upd->execute();

            echo json_encode([
                "ok" => true,
                "user" => [
                    "rol" => $rolAsignado,
                    "nombre" => $row['nombre'],
                    "matricula" => $row['matricula'],
                    "carrera" => $carreraFinal,
                    "correo" => $row['correo'],
                    "grupo" => $grupoFinal
                ]
            ]);
        } else {
            echo json_encode(["ok" => false, "error" => "Matrícula/correo o contraseña incorrectos."]);
        }
        break;

    case "registro_instructor":
        $nombre = trim($_POST['nombre'] ?? '');
        $identificacion = trim($_POST['identificacion'] ?? '');
        $correo = trim($_POST['correo'] ?? '');
        $taller = trim($_POST['taller_impartido'] ?? '');
        $contrasena = $_POST['contrasena'] ?? '';
        $rol = "Instructor";

        $stmt = $conn->prepare("INSERT INTO datosInstructor (nombre, identificacion, correo, taller_impartido, contrasena, rol) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $nombre, $identificacion, $correo, $taller, $contrasena, $rol);
        if ($stmt->execute()) {
            echo json_encode([
                "ok" => true,
                "user" => [
                    "rol" => $rol,
                    "nombre" => $nombre,
                    "identificacion" => $identificacion,
                    "correo" => $correo,
                    "taller_impartido" => $taller
                ]
            ]);
        } else {
            echo json_encode(["ok" => false, "error" => "Identificación o correo ya registrado: " . $stmt->error]);
        }
        break;

    case "login_instructor":
        $identificador = trim($_POST['identificador'] ?? '');
        $contrasena = $_POST['contrasena'] ?? '';

        $stmt = $conn->prepare("SELECT id, nombre, identificacion, correo, taller_impartido, rol FROM datosInstructor WHERE (identificacion = ? OR correo = ?) AND contrasena = ? LIMIT 1");
        $stmt->bind_param("sss", $identificador, $identificador, $contrasena);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            $rolAsignado = "Instructor";
            $upd = $conn->prepare("UPDATE datosInstructor SET rol = ? WHERE id = ?");
            $upd->bind_param("si", $rolAsignado, $row['id']);
            $upd->execute();

            echo json_encode([
                "ok" => true,
                "user" => [
                    "rol" => $rolAsignado,
                    "nombre" => $row['nombre'],
                    "identificacion" => $row['identificacion'],
                    "correo" => $row['correo'],
                    "taller_impartido" => $row['taller_impartido']
                ]
            ]);
        } else {
            echo json_encode(["ok" => false, "error" => "Identificación/correo o contraseña incorrectos."]);
        }
        break;

    case "datos_alumno":
        $matricula = trim($_GET['matricula'] ?? '20241001');
        $stmt = $conn->prepare("SELECT nombre, matricula, carrera, correo, grupo, rol FROM datosEstudiante WHERE matricula = ? LIMIT 1");
        $stmt->bind_param("s", $matricula);
        $stmt->execute();
        $alumno = $stmt->get_result()->fetch_assoc();

        $stmtT = $conn->prepare("SELECT taller, fecha_inscripcion FROM taller WHERE matricula = ? ORDER BY id DESC LIMIT 1");
        $stmtT->bind_param("s", $matricula);
        $stmtT->execute();
        $inscripcion = $stmtT->get_result()->fetch_assoc();

        $tallerActual = $inscripcion['taller'] ?? 'Diseño y dibujo artístico';
        $stmtA = $conn->prepare("SELECT fecha, estado_asistencia FROM listaAsist WHERE matricula = ? ORDER BY fecha DESC");
        $stmtA->bind_param("s", $matricula);
        $stmtA->execute();
        $asistencias = $stmtA->get_result()->fetch_all(MYSQLI_ASSOC);

        $stmtC = $conn->prepare("SELECT matricula, nombre, taller, fecha_emision, constancia FROM hasConstancia WHERE matricula = ?");
        $stmtC->bind_param("s", $matricula);
        $stmtC->execute();
        $constancias = $stmtC->get_result()->fetch_all(MYSQLI_ASSOC);

        $stmtJ = $conn->prepare("SELECT fecha, justificante, estado, observaciones FROM estadoJust WHERE matricula = ? ORDER BY id DESC");
        $stmtJ->bind_param("s", $matricula);
        $stmtJ->execute();
        $justificaciones = $stmtJ->get_result()->fetch_all(MYSQLI_ASSOC);

        echo json_encode([
            "ok" => true,
            "alumno" => $alumno,
            "tallerActual" => $tallerActual,
            "asistencias" => $asistencias,
            "constancias" => $constancias,
            "justificaciones" => $justificaciones
        ]);
        break;

    case "editar_alumno":
        $matricula = trim($_POST['matricula'] ?? '');
        $correo = trim($_POST['correo'] ?? '');
        $carrera = trim($_POST['carrera'] ?? '');
        $grupo = trim($_POST['grupo'] ?? '');

        $stmt = $conn->prepare("UPDATE datosEstudiante SET correo = ?, carrera = ?, grupo = ? WHERE matricula = ?");
        $stmt->bind_param("ssss", $correo, $carrera, $grupo, $matricula);
        $ok = $stmt->execute();
        echo json_encode(["ok" => $ok, "error" => $ok ? null : $stmt->error]);
        break;

    case "inscribir_taller":
        $matricula = trim($_POST['matricula'] ?? '');
        $taller = trim($_POST['taller'] ?? '');

        $stmt = $conn->prepare("INSERT INTO taller (taller, matricula) VALUES (?, ?) ON DUPLICATE KEY UPDATE taller = VALUES(taller)");
        $stmt->bind_param("ss", $taller, $matricula);
        $ok = $stmt->execute();

        // Sincroniza en constanciasTaller si aún no existe
        $stmtAl = $conn->prepare("SELECT nombre FROM datosEstudiante WHERE matricula = ? LIMIT 1");
        $stmtAl->bind_param("s", $matricula);
        $stmtAl->execute();
        $al = $stmtAl->get_result()->fetch_assoc();
        if ($al) {
            $stmtConst = $conn->prepare("INSERT IGNORE INTO constanciasTaller (matricula, nombre, taller, estado, constancia) VALUES (?, ?, ?, 'Sin constancia', NULL)");
            $stmtConst->bind_param("sss", $matricula, $al['nombre'], $taller);
            $stmtConst->execute();
        }

        echo json_encode(["ok" => $ok, "error" => $ok ? null : $stmt->error]);
        break;

    case "solicitar_justificante":
        $matricula = trim($_POST['matricula'] ?? '20241001');
        $nombre = trim($_POST['nombre'] ?? '');
        $fecha = trim($_POST['fecha'] ?? '');
        $taller = trim($_POST['taller'] ?? 'Diseño y dibujo artístico');

        $up = uploadFile("justificante", "justificantes", ["pdf"]);
        if (is_array($up) && isset($up['error'])) {
            echo json_encode(["ok" => false, "error" => $up['error']]);
            exit;
        }
        $archivoNombre = $up ?: ($_POST['nombre_archivo_texto'] ?? 'justificante.pdf');

        $stmt1 = $conn->prepare("INSERT INTO solicitudJustificar (matricula, nombre, taller, fecha, justificante) VALUES (?, ?, ?, ?, ?)");
        $stmt1->bind_param("sssss", $matricula, $nombre, $taller, $fecha, $archivoNombre);
        $stmt1->execute();

        $stmt2 = $conn->prepare("INSERT INTO estadoJust (matricula, nombre, fecha, justificante, estado, observaciones) VALUES (?, ?, ?, ?, 'Pendiente', '')");
        $stmt2->bind_param("ssss", $matricula, $nombre, $fecha, $archivoNombre);
        $stmt2->execute();

        $stmt3 = $conn->prepare("INSERT INTO justificar (matricula, nombre, taller, fecha, justificante, estado, observaciones) VALUES (?, ?, ?, ?, ?, 'Pendiente', '')");
        $stmt3->bind_param("sssss", $matricula, $nombre, $taller, $fecha, $archivoNombre);
        $stmt3->execute();

        echo json_encode(["ok" => true, "archivo" => $archivoNombre]);
        break;

    case "datos_instructor":
        $taller = trim($_GET['taller'] ?? 'Diseño y dibujo artístico');
        $identificacion = trim($_GET['identificacion'] ?? 'INST-001');

        $stmtInst = $conn->prepare("SELECT nombre, identificacion, correo, taller_impartido, rol FROM datosInstructor WHERE identificacion = ? OR taller_impartido = ? LIMIT 1");
        $stmtInst->bind_param("ss", $identificacion, $taller);
        $stmtInst->execute();
        $instructor = $stmtInst->get_result()->fetch_assoc();

        $stmtInsc = $conn->prepare("
            SELECT e.matricula, e.nombre, e.carrera, e.grupo, t.taller
            FROM taller t
            INNER JOIN datosEstudiante e ON e.matricula = t.matricula
            WHERE t.taller = ?
            ORDER BY e.nombre ASC
        ");
        $stmtInsc->bind_param("s", $taller);
        $stmtInsc->execute();
        $inscritos = $stmtInsc->get_result()->fetch_all(MYSQLI_ASSOC);

        $stmtConst = $conn->prepare("SELECT id, matricula, nombre, taller, estado, constancia FROM constanciasTaller WHERE taller = ? ORDER BY matricula ASC");
        $stmtConst->bind_param("s", $taller);
        $stmtConst->execute();
        $constanciasTaller = $stmtConst->get_result()->fetch_all(MYSQLI_ASSOC);

        $stmtHas = $conn->prepare("SELECT matricula, nombre, taller, fecha_emision, constancia FROM hasConstancia WHERE taller = ? ORDER BY id DESC");
        $stmtHas->bind_param("s", $taller);
        $stmtHas->execute();
        $hasConstancia = $stmtHas->get_result()->fetch_all(MYSQLI_ASSOC);

        $stmtFecha = $conn->prepare("SELECT MAX(fecha) AS ultima_fecha FROM tomaAsist WHERE taller = ?");
        $stmtFecha->bind_param("s", $taller);
        $stmtFecha->execute();
        $ultimaFecha = $stmtFecha->get_result()->fetch_assoc()['ultima_fecha'] ?? date('Y-m-d');

        $stmtLista = $conn->prepare("SELECT taller, fecha, matricula, nombre, estado_asistencia FROM listaAsist WHERE taller = ? AND fecha = ? ORDER BY matricula ASC");
        $stmtLista->bind_param("ss", $taller, $ultimaFecha);
        $stmtLista->execute();
        $listaAsist = $stmtLista->get_result()->fetch_all(MYSQLI_ASSOC);

        $stmtJust = $conn->prepare("SELECT id, matricula, nombre, taller, fecha, justificante, estado, observaciones FROM justificar WHERE taller = ? OR taller = 'Diseño y dibujo artístico' ORDER BY id DESC");
        $stmtJust->bind_param("s", $taller);
        $stmtJust->execute();
        $justificantes = $stmtJust->get_result()->fetch_all(MYSQLI_ASSOC);

        echo json_encode([
            "ok" => true,
            "instructor" => $instructor,
            "inscritos" => $inscritos,
            "constanciasTaller" => $constanciasTaller,
            "hasConstancia" => $hasConstancia,
            "ultimaFecha" => $ultimaFecha,
            "listaAsist" => $listaAsist,
            "justificantes" => $justificantes
        ]);
        break;

    case "editar_instructor":
        $identificacion = trim($_POST['identificacion'] ?? 'INST-001');
        $correo = trim($_POST['correo'] ?? '');
        $talleres = $_POST['talleres'] ?? '';
        if (is_array($talleres)) {
            $talleres = implode(", ", $talleres);
        }
        $stmt = $conn->prepare("UPDATE datosInstructor SET correo = ?, taller_impartido = ? WHERE identificacion = ?");
        $stmt->bind_param("sss", $correo, $talleres, $identificacion);
        $ok = $stmt->execute();
        echo json_encode(["ok" => $ok, "taller_impartido" => $talleres]);
        break;

    case "subir_constancia":
        $matricula = trim($_POST['matricula'] ?? '');
        $nombre = trim($_POST['nombre'] ?? '');
        $taller = trim($_POST['taller'] ?? 'Diseño y dibujo artístico');
        $fechaEmision = date("F Y");

        $up = uploadFile("constancia", "constancias", ["pdf"]);
        if (is_array($up) && isset($up['error'])) {
            echo json_encode(["ok" => false, "error" => $up['error']]);
            exit;
        }
        $rutaPdf = $up ?: "imgs/constacia.pdf";

        $stmt1 = $conn->prepare("
            INSERT INTO constanciasTaller (matricula, nombre, taller, estado, constancia)
            VALUES (?, ?, ?, 'Con constancia', ?)
            ON DUPLICATE KEY UPDATE estado = 'Con constancia', constancia = VALUES(constancia), nombre = VALUES(nombre)
        ");
        $stmt1->bind_param("ssss", $matricula, $nombre, $taller, $rutaPdf);
        $stmt1->execute();

        $stmt2 = $conn->prepare("INSERT INTO hasConstancia (matricula, nombre, taller, fecha_emision, constancia) VALUES (?, ?, ?, ?, ?)");
        $stmt2->bind_param("sssss", $matricula, $nombre, $taller, $fechaEmision, $rutaPdf);
        $stmt2->execute();

        echo json_encode(["ok" => true, "constancia" => $rutaPdf, "fecha_emision" => $fechaEmision]);
        break;

    case "publicar_aviso":
        $titulo = trim($_POST['titulo_aviso'] ?? '');
        $contenido = trim($_POST['contenido'] ?? '');
        $taller = trim($_POST['taller'] ?? 'General');

        $up = uploadFile("imagen", "avisos", ["jpg", "jpeg", "png", "webp", "gif"]);
        if (is_array($up) && isset($up['error'])) {
            echo json_encode(["ok" => false, "error" => $up['error']]);
            exit;
        }
        $rutaImg = $up ?: null;

        $stmt = $conn->prepare("INSERT INTO avisos (titulo_aviso, contenido, imagen, taller) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $titulo, $contenido, $rutaImg, $taller);
        $ok = $stmt->execute();
        echo json_encode(["ok" => $ok, "imagen" => $rutaImg]);
        break;

    case "listar_avisos":
        $res = $conn->query("SELECT id, titulo_aviso, contenido, imagen, taller, fecha_publicacion FROM avisos ORDER BY id DESC");
        echo json_encode(["ok" => true, "avisos" => $res->fetch_all(MYSQLI_ASSOC)]);
        break;

    case "guardar_asistencia":
        $raw = json_decode(file_get_contents("php://input"), true);
        $taller = trim($raw['taller'] ?? 'Diseño y dibujo artístico');
        $fecha = trim($raw['fecha'] ?? date('Y-m-d'));
        $registros = $raw['registros'] ?? [];

        foreach ($registros as $item) {
            $matricula = trim($item['matricula'] ?? '');
            $nombre = trim($item['nombre'] ?? '');
            $tipo = $item['tipo'] ?? 'asistencia'; // asistencia | falta | falta_justificada

            $incAsist = ($tipo === 'asistencia') ? 1 : 0;
            $incFalta = ($tipo === 'falta') ? 1 : 0;
            $incJust  = ($tipo === 'falta_justificada') ? 1 : 0;
            $estadoLista = ($tipo === 'asistencia' || $tipo === 'falta_justificada') ? 'Presente' : 'Ausente';

            $stmtT = $conn->prepare("
                INSERT INTO tomaAsist (taller, fecha, matricula, nombre, asistencia, falta, falta_justificada)
                VALUES (?, ?, ?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE
                    asistencia = asistencia + VALUES(asistencia),
                    falta = falta + VALUES(falta),
                    falta_justificada = falta_justificada + VALUES(falta_justificada)
            ");
            $stmtT->bind_param("ssssiii", $taller, $fecha, $matricula, $nombre, $incAsist, $incFalta, $incJust);
            $stmtT->execute();

            $stmtL = $conn->prepare("
                INSERT INTO listaAsist (taller, fecha, matricula, nombre, estado_asistencia)
                VALUES (?, ?, ?, ?, ?)
                ON DUPLICATE KEY UPDATE estado_asistencia = VALUES(estado_asistencia)
            ");
            $stmtL->bind_param("sssss", $taller, $fecha, $matricula, $nombre, $estadoLista);
            $stmtL->execute();
        }

        echo json_encode(["ok" => true, "fecha" => $fecha]);
        break;

    case "revisar_justificante":
        $nombre = trim($_POST['nombre'] ?? '');
        $decision = trim($_POST['resultado'] ?? '');
        $observaciones = trim($_POST['observaciones'] ?? '');

        $estado = ($decision === 'Aceptar') ? 'Aceptado' : 'Rechazado';

        $stmt1 = $conn->prepare("UPDATE justificar SET estado = ?, observaciones = ? WHERE nombre = ? ORDER BY id DESC LIMIT 1");
        $stmt1->bind_param("sss", $estado, $observaciones, $nombre);
        $stmt1->execute();

        $stmt2 = $conn->prepare("UPDATE estadoJust SET estado = ?, observaciones = ? WHERE nombre = ? ORDER BY id DESC LIMIT 1");
        $stmt2->bind_param("sss", $estado, $observaciones, $nombre);
        $stmt2->execute();

        echo json_encode(["ok" => true, "estado" => $estado]);
        break;

    default:
        echo json_encode(["ok" => false, "error" => "Acción no válida"]);
}
