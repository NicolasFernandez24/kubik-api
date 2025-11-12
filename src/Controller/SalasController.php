<?php
namespace App\Controller;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Database\Database;

class SalasController{
     private $db;

    public function __construct(Database $database)
    {
        $this->db = $database->getConnection();
    }

    function GetAllSalas(Request $request, Response $response): Response
{
    $stmt = $this->db->query("SELECT * FROM salas");
    $salas = $stmt->fetchAll();

    // Decodificar campo imagenes
    foreach ($salas as &$sala) {
        if (isset($sala['imagenes'])) {
            $sala['imagenes'] = json_decode($sala['imagenes'], true);
        }
    }

    $response->getBody()->write(json_encode($salas));
    return $response->withHeader('Content-Type', 'application/json');
}

public function getSalasById(Request $request, Response $response, array $args): Response
{
    $id = $args['id'];
    $stmt = $this->db->prepare("SELECT * FROM salas WHERE id = ?");
    $stmt->execute([$id]);
    $sala = $stmt->fetch();

    if ($sala) {
        if (isset($sala['imagenes'])) {
            $sala['imagenes'] = json_decode($sala['imagenes'], true);
        }
        $response->getBody()->write(json_encode($sala));
    } else {
        $response->getBody()->write(json_encode(['error' => 'Sala no encontrada']));
        return $response->withStatus(404);
    }

    return $response->withHeader('Content-Type', 'application/json');
}


 public function createSala(Request $request, Response $response): Response
{
    error_log("🟡 [createSala] Inicio del método");

    $parsedBody = $request->getParsedBody();
    $uploadedFiles = $request->getUploadedFiles();

   
    $basePath = 'C:/Users/Equipo/Desktop/kubik/src/assets/salas/';

  
    if (!is_dir($basePath)) {
        mkdir($basePath, 0777, true);
        error_log("📁 [createSala] Carpeta creada: $basePath");
    }

    
    if (
        empty($parsedBody['nombre']) ||
        empty($parsedBody['descripcion']) ||
        empty($parsedBody['capacidad']) ||
        empty($parsedBody['precio']) ||
        !isset($parsedBody['disponibilidad']) ||
        !isset($uploadedFiles['imagenFondo']) ||
        !isset($uploadedFiles['imagenes'])
    ) {
        error_log("❌ [createSala] Faltan campos obligatorios");
        $response->getBody()->write(json_encode([
            'error' => 'Faltan campos obligatorios.',
            'received_data' => $parsedBody
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    // Imagen de fondo
    $imagenFondo = $uploadedFiles['imagenFondo'];
    if ($imagenFondo->getError() === UPLOAD_ERR_OK) {
        $nombreFondo = uniqid('fondo_') . '_' . $imagenFondo->getClientFilename();
        $rutaFondo = $basePath . $nombreFondo;
        error_log("📂 [createSala] Guardando imagen de fondo en: $rutaFondo");
        $imagenFondo->moveTo($rutaFondo);
    } else {
        error_log("❌ [createSala] Error al subir imagenFondo. Código: " . $imagenFondo->getError());
        $response->getBody()->write(json_encode(['error' => 'Error al subir imagenFondo']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }

    // Imágenes múltiples
    $imagenesRutas = [];
    foreach ($uploadedFiles['imagenes'] as $imagen) {
        if ($imagen->getError() === UPLOAD_ERR_OK) {
            $nombreImagen = uniqid('img_') . '_' . $imagen->getClientFilename();
            $rutaImagen = $basePath . $nombreImagen;
            error_log("📂 [createSala] Guardando imagen adicional en: $rutaImagen");
            $imagen->moveTo($rutaImagen);
            // Guardamos la ruta relativa para usarla en el front
            $imagenesRutas[] = 'assets/salas/' . $nombreImagen;
        } else {
            error_log("⚠️ [createSala] Error al subir una imagen adicional. Código: " . $imagen->getError());
        }
    }

    $disponibilidad = filter_var($parsedBody['disponibilidad'], FILTER_VALIDATE_BOOLEAN) ? 1 : 0;

    try {
        error_log("🧾 [createSala] Ejecutando INSERT en la base de datos");

        $stmt = $this->db->prepare("
            INSERT INTO salas (nombre, descripcion, capacidad, precio, disponibilidad, imageFondo, imagenes)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $parsedBody['nombre'],
            $parsedBody['descripcion'],
            $parsedBody['capacidad'],
            $parsedBody['precio'],
            $disponibilidad,
            'assets/salas/' . $nombreFondo,        
            json_encode($imagenesRutas)
        ]);

        error_log("✅ [createSala] Sala creada correctamente");

        $response->getBody()->write(json_encode(['message' => 'Sala creada correctamente']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
    } catch (\PDOException $e) {
        error_log("❌ [createSala] Error PDO: " . $e->getMessage());
        $response->getBody()->write(json_encode([
            'error' => 'Error al crear sala',
            'exception' => $e->getMessage()
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
}


public function updateSala(Request $request, Response $response, array $args): Response
{
    $id = $args['id'];
    error_log("[updateSala] Inicio del método para sala ID: $id");

    $data = $request->getParsedBody() ?? [];
    $uploadedFiles = $request->getUploadedFiles();
    error_log("[updateSala] Datos recibidos (parsedBody): " . var_export($data, true));
    error_log("[updateSala] Archivos subidos (uploadedFiles): " . var_export(array_keys($uploadedFiles), true));

    $basePath = 'C:/Users/Equipo/Desktop/kubik/src/assets/salas/';
    if (!is_dir($basePath)) {
        mkdir($basePath, 0777, true);
        error_log("[updateSala] Carpeta creada: $basePath");
    }

    // Validar campos obligatorios
    $nombre = $data['nombre'] ?? null;
    $descripcion = $data['descripcion'] ?? null;
    $capacidad = $data['capacidad'] ?? null;
    $precio = $data['precio'] ?? null;
    $disponibilidad = isset($data['disponibilidad']) ? (int)$data['disponibilidad'] : null;

    error_log("[updateSala] nombre=" . var_export($nombre, true));
    error_log("[updateSala] descripcion=" . var_export($descripcion, true));
    error_log("[updateSala] capacidad=" . var_export($capacidad, true));
    error_log("[updateSala] precio=" . var_export($precio, true));
    error_log("[updateSala] disponibilidad=" . var_export($disponibilidad, true));

    if (!$nombre || !$descripcion || !$capacidad || !$precio || $disponibilidad === null) {
        error_log("[updateSala] ❌ Faltan campos obligatorios");
        $response->getBody()->write(json_encode([
            'error' => 'Faltan campos obligatorios',
            'received_data' => $data
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $imagenFondoPath = null;
    if (isset($uploadedFiles['imagenFondo'])) {
        $imagenFondo = $uploadedFiles['imagenFondo'];
        error_log("[updateSala] imagenFondo error code: " . $imagenFondo->getError());
        if ($imagenFondo->getError() === UPLOAD_ERR_OK) {
            $filename = uniqid('fondo_') . '_' . $imagenFondo->getClientFilename();
            try {
                $imagenFondo->moveTo($basePath . $filename);
                $imagenFondoPath = 'assets/salas/' . $filename;
                error_log("[updateSala] Imagen de fondo guardada en: $imagenFondoPath");
            } catch (\Exception $e) {
                error_log("[updateSala] ❌ Error al mover imagenFondo: " . $e->getMessage());
            }
        }
    }

    $imagenesPaths = [];
    if (isset($uploadedFiles['imagenes']) && is_array($uploadedFiles['imagenes'])) {
        foreach ($uploadedFiles['imagenes'] as $imagen) {
            error_log("[updateSala] Imagen adicional error code: " . $imagen->getError());
            if ($imagen->getError() === UPLOAD_ERR_OK) {
                $filename = uniqid('img_') . '_' . $imagen->getClientFilename();
                try {
                    $imagen->moveTo($basePath . $filename);
                    $imagenesPaths[] = 'assets/salas/' . $filename;
                    error_log("[updateSala] Imagen adicional guardada en: " . end($imagenesPaths));
                } catch (\Exception $e) {
                    error_log("[updateSala] ❌ Error al mover imagen adicional: " . $e->getMessage());
                }
            }
        }
    }

    $imagenesStr = !empty($imagenesPaths) ? json_encode($imagenesPaths) : null;

    try {
        $sql = "UPDATE salas SET nombre=?, descripcion=?, capacidad=?, precio=?, disponibilidad=?";
        $params = [$nombre, $descripcion, $capacidad, $precio, $disponibilidad];

        if ($imagenFondoPath) {
            $sql .= ", imageFondo=?";
            $params[] = $imagenFondoPath;
        }
        if ($imagenesStr) {
            $sql .= ", imagenes=?";
            $params[] = $imagenesStr;
        }

        $sql .= " WHERE id=?";
        $params[] = $id;

        error_log("[updateSala] SQL: $sql");
        error_log("[updateSala] Params: " . var_export($params, true));

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        error_log("[updateSala] ✅ Sala actualizada correctamente");
        $response->getBody()->write(json_encode(['message' => 'Sala actualizada']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } catch (\PDOException $e) {
        error_log("[updateSala] ❌ Error PDO: " . $e->getMessage());
        $response->getBody()->write(json_encode([
            'error' => 'Error al actualizar sala',
            'exception' => $e->getMessage()
        ]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
}


public function deleteSala(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        $stmt = $this->db->prepare("DELETE FROM salas WHERE id = ?");
        $stmt->execute([$id]);
        $response->getBody()->write(json_encode(['message' => 'sla eliminado']));
        return $response->withHeader('Content-Type', 'application/json');
    }

}