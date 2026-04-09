## Sistema de Control de Acceso RFID con ESP32

## Descripción
Sistema de control de acceso basado en tecnología RFID utilizando un ESP32 y el módulo MFRC522. El sistema lee tarjetas RFID, consulta una base de datos remota mediante HTTP y proporciona retroalimentación visual (LEDs) y sonora (buzzer) según el resultado de la validación.

---

## Arquitectura del sistema

1. El usuario acerca una tarjeta o llavero RFID  
2. El ESP32 lee el UID mediante el RC522  
3. Se envía una petición HTTP al servidor  
4. El servidor consulta la base de datos  
5. Retorna autorización  
6. El ESP32 activa LEDs y buzzer según el resultado  

---

## Hardware requerido

| Componente | Cantidad |
|------------|----------|
| ESP32 DevKit V1 | 1 |
| Módulo RFID RC522 | 1 |
| Tarjetas RFID Mifare Classic 1K | 2 |
| LEDs (cátodo común) | 3 |
| Buzzer activo | 1 |
| Resistencias 220Ω | 3 |
| Protoboard | 1 |
| Cables Dupont | varios |

---

## Conexiones

### RFID RC522 → ESP32

| RC522 | ESP32 |
|-------|-------|
| SDA   | GPIO5  |
| SCK   | GPIO18 |
| MOSI  | GPIO23 |
| MISO  | GPIO19 |
| RST   | GPIO22 |
| 3.3V  | 3V3    |
| GND   | GND    |

---

### LED RGB y Buzzer

| Componente | GPIO |
|------------|------|
| LED Rojo   | GPIO25 |
| LED Verde  | GPIO26 |
| LED Azul   | GPIO27 |
| Buzzer     | GPIO32 |

---

## Backend

- Servidor: PHP  
- Base de datos: MySQL  
- Endpoint: `/api.php`  
- Método: HTTP (GET o POST)  

---

## Ejemplo de respuesta del servidor

Respuesta válida:
Conectado

Respuesta inválida:
Acceso Denegado

## Configuración

Editar el archivo de configuración del firmware (main.c):

// Credenciales de la red WiFi (modificar segun entorno)
const char* ssid     = "CELERITY-REF56";      // Nombre de la red WiFi
const char* password = "RxA2@PzL";            // Contraseña de la red

// Endpoint del servidor REST API (donde se verifica el UID)
const char* serverUrl = "http://192.168.1.72:8080/api.php";

