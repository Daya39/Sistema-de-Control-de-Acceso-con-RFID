#include <WiFi.h>          // Libreria para conexion WiFi en ESP32
#include <HTTPClient.h>    // Libreria para peticiones HTTP
#include <SPI.h>           // Libreria para bus SPI (comunicacion con RC522)
#include <MFRC522.h>       // Libreria para el modulo lector RFID RC522

/* ================================================================
 * DEFINICION DE PINES
 * ================================================================ */

// Pines para el modulo RFID RC522 (comunicacion SPI)
#define SS_PIN    5   // SDA (Chip Select) -> GPIO5 del ESP32
#define RST_PIN   22  // Pin de Reset -> GPIO22 del ESP32

// Pin para el buzzer pasivo (emite tono cuando recibe señal PWM)
#define BUZZER_PIN 32  // GPIO32 conectado al positivo del buzzer

// Pines para los LEDs de indicacion (anodo comun o catodo comun segun conexion)
#define LED_AZUL  27   // LED azul: indica que el WiFi esta conectado
#define LED_VERDE 26   // LED verde: se enciende cuando el acceso es CONCEDIDO
#define LED_ROJO  25   // LED rojo: se enciende cuando el acceso es DENEGADO

/* ================================================================
 * CONFIGURACION DE RED Y SERVIDOR
 * ================================================================ */

// Credenciales de la red WiFi (modificar segun entorno)
const char* ssid     = "CELERITY-REF56";      // Nombre de la red WiFi
const char* password = "RxA2@PzL";            // Contraseña de la red

// Endpoint del servidor REST API (donde se verifica el UID)
const char* serverUrl = "http://192.168.1.72:8080/api.php";

// Tiempo maximo de espera para respuestas HTTP (en milisegundos)
const unsigned long timeout = 5000;

/* ================================================================
 * INSTANCIACION DE OBJETOS
 * ================================================================ */

// Objeto MFRC522: gestiona la comunicacion con el lector RFID
// Parametros: pin SS (Chip Select) y pin RST (Reset)
MFRC522 rfid(SS_PIN, RST_PIN);

/* ================================================================
 * FUNCION setup()
 * Se ejecuta una sola vez al iniciar el ESP32
 * ================================================================ */
void setup() {
  // Inicializar comunicacion serie para depuracion (115200 baudios recomendado)
  Serial.begin(9600);
  delay(1000);  // Espera estabilizacion

  // --- Inicializacion del bus SPI y del modulo RFID ---
  SPI.begin();           
  rfid.PCD_Init();       

  // --- Configuracion de pines de salida ---
  pinMode(BUZZER_PIN, OUTPUT);
  digitalWrite(BUZZER_PIN, LOW);

  pinMode(LED_AZUL, OUTPUT);
  pinMode(LED_VERDE, OUTPUT);
  pinMode(LED_ROJO, OUTPUT);
  digitalWrite(LED_AZUL, LOW);
  digitalWrite(LED_VERDE, LOW);
  digitalWrite(LED_ROJO, LOW);

  // --- Conexion a la red WiFi ---
  WiFi.begin(ssid, password);
  Serial.print("Conectando a WiFi");

  unsigned long startTime = millis();
  while (WiFi.status() != WL_CONNECTED && millis() - startTime < 10000) {
    delay(500);
    Serial.print(".");
  }

  if (WiFi.status() != WL_CONNECTED) {
    Serial.println("\nError de conexion WiFi!");
    digitalWrite(LED_AZUL, LOW);
  } else {
    Serial.println("\nConectado! IP: " + WiFi.localIP().toString());
    digitalWrite(LED_AZUL, HIGH);
  }
}

/* ================================================================
 * FUNCION loop()
 * ================================================================ */
void loop() {
  if (!rfid.PICC_IsNewCardPresent() || !rfid.PICC_ReadCardSerial()) {
    delay(10);
    return;
  }

  String uid = "";
  for (byte i = 0; i < rfid.uid.size; i++) {
    char buffer[3];
    sprintf(buffer, "%02X", rfid.uid.uidByte[i]);
    uid += buffer;
  }

  Serial.println("UID detectado: " + uid);

  if (WiFi.status() == WL_CONNECTED) {
    WiFiClient client;
    HTTPClient http;

    String fullUrl = String(serverUrl) + "?uid=" + uid;
    http.begin(client, fullUrl);
    http.setTimeout(timeout);

    int httpCode = http.GET();

    if (httpCode == HTTP_CODE_OK) {
      String response = http.getString();
      response.trim();

      Serial.println("Respuesta del servidor: " + response);

      if (response.equalsIgnoreCase("GRANTED")) {
        activarBuzzerPermitido(3000);
      } else {
        Serial.println("Acceso denegado!");
        activarBuzzerDenegado();
      }
    } else {
      Serial.printf("Error HTTP: %d\n", httpCode);
    }

    http.end();
  } else {
    Serial.println("Sin conexion WiFi!");
  }

  rfid.PICC_HaltA();
  rfid.PCD_StopCrypto1();

  delay(200);
}

/* ================================================================
 * FUNCIONES DE RETROALIMENTACION
 * ================================================================ */

void activarBuzzerPermitido(unsigned int duration) {
  digitalWrite(BUZZER_PIN, HIGH);
  digitalWrite(LED_VERDE, HIGH);
  Serial.println("Buzzer y LED VERDE ACTIVADOS");

  delay(duration);

  digitalWrite(BUZZER_PIN, LOW);
  digitalWrite(LED_VERDE, LOW);
  Serial.println("Buzzer y LED VERDE DESACTIVADOS");
}

void activarBuzzerDenegado() {
  for (int i = 0; i < 3; i++) {
    digitalWrite(BUZZER_PIN, HIGH);
    digitalWrite(LED_ROJO, HIGH);
    Serial.println("Buzzer y LED ROJO ACTIVADOS");

    delay(1000);

    digitalWrite(BUZZER_PIN, LOW);
    digitalWrite(LED_ROJO, LOW);
    Serial.println("Buzzer y LED ROJO DESACTIVADOS");

    if (i < 2) delay(1000);
  }
}
