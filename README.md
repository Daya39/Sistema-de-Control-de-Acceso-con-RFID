# 🔐 Sistema de Control de Acceso RFID con ESP32

## 📋 Descripción

Sistema de control de acceso basado en tecnología RFID utilizando un ESP32 y el módulo MFRC522. El sistema lee tarjetas RFID, consulta una base de datos remota mediante HTTP y proporciona retroalimentación visual (LED RGB) y sonora (buzzer) según el resultado de la validación.

---

## 🏗️ Arquitectura del sistema

<img width="1637" height="365" alt="Arquitectura del sistema" src="https://github.com/user-attachments/assets/9ebb891a-4c34-4535-9362-b1389edb0fbf" />

### 🔄 Flujo del sistema

1. El usuario acerca una tarjeta o llavero RFID  
2. El ESP32 lee el UID mediante el módulo RC522  
3. Se envía una petición HTTP GET al servidor con el UID  
4. El servidor consulta la base de datos  
5. Retorna autorización (`GRANTED` / `DENIED`)  
6. El ESP32 activa LED y buzzer según el resultado  

---

## 🛠️ Hardware requerido

| Componente | Cantidad | Observaciones |
|------------|----------|---------------|
| ESP32 DevKit V1 | 1 | 30 o 38 pines |
| Módulo RFID RC522 (MFRC522) | 1 | Incluye tarjeta y llavero |
| Tarjetas RFID Mifare Classic 1K | 2 | 13.56 MHz |
| LEDs | 3 | Indicadores de estado |
| Buzzer activo 5V | 1 | Incluye oscilador interno |
| Resistencias 220Ω | 3 | Para cada LED |
| Protoboard | 1 | 830 puntos mínimo |
| Cables Dupont | varios | M-M / M-H |
| Cable USB tipo-C | 1 | Programación |

---

## 🔌 Conexiones

### RFID RC522 → ESP32 (SPI)

| Pin RC522 | Función | GPIO ESP32 |
|-----------|--------|------------|
| SDA (SS)  | CS     | GPIO5  |
| SCK       | Clock  | GPIO18 |
| MOSI      | Data   | GPIO23 |
| MISO      | Data   | GPIO19 |
| RST       | Reset  | GPIO22 |
| 3.3V      | VCC    | 3V3    |
| GND       | GND    | GND    |

---

### LED RGB (cátodo común) y Buzzer

| Componente | GPIO |
|------------|------|
| LED Rojo   | GPIO25 |
| LED Verde  | GPIO26 |
| LED Azul   | GPIO27 |
| Buzzer     | GPIO32 |

---

### ⚠️ Consideraciones de hardware

- El ESP32 trabaja a **3.3V**
- No conectar directamente dispositivos de 5V sin adaptación
- Para buzzer de 5V:
  - usar transistor (recomendado)
  - o verificar compatibilidad a 3.3V

---

## 🌐 Backend

El sistema utiliza un servidor web para validar los UID.

**Tecnologías:**
- PHP  
- MySQL  
- Docker  

**Endpoint:**
http://IP_SERVIDOR/api.php?uid=XXXXXXXX

**Respuesta del servidor:**
- `GRANTED` → acceso permitido  
- `DENIED` → acceso denegado  

---

## ⚙️ Configuración del firmware

Editar el archivo correspondiente:

```c
#define WIFI_SSID "tu_red"
#define WIFI_PASS "tu_password"
#define SERVER_URL "http://IP_SERVIDOR/api.php"

🐳 Despliegue del servidor con Docker

🚀 Levantar el servidor

Ubicarse en la carpeta y ejecutar:

docker-compose up -d

🛑 Detener el servidor

docker-compose down
