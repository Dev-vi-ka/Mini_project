#include <WiFi.h>
#include <ESP32Servo.h>

Servo dispenserServo;

const char* ssid = "Infinix NOTE 40 5G";
const char* password = "Samsunga20s@";

WiFiServer server(80);

// Optional LED and Buzzer pins
#define LED_PIN 2
#define BUZZER_PIN 4
#define SERVO_PIN 13

void setup() {
  Serial.begin(115200);
  Serial.println("\n🧃 Smart Vending Machine (Dynamic IP Mode) Booting...");

  pinMode(LED_PIN, OUTPUT);
  pinMode(BUZZER_PIN, OUTPUT);
  digitalWrite(LED_PIN, LOW);
  digitalWrite(BUZZER_PIN, LOW);

  // Connect to WiFi (NO STATIC IP)
  WiFi.begin(ssid, password);
  Serial.print("📡 Connecting to WiFi");

  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }

  Serial.println("\n✅ WiFi Connected!");
  Serial.print("🌐 Assigned IP Address: ");
  Serial.println(WiFi.localIP());   // <-- DYNAMIC IP PRINTED HERE

  // Attach continuous servo
  dispenserServo.attach(SERVO_PIN);
  dispenserServo.write(90);  // Stop position
  Serial.println("⚙ Servo ready (neutral 90°)");

  server.begin();
  Serial.println("🖥 Web server started...");
}

void dispenseItem() {
  Serial.println("🎯 Vending triggered!");

  digitalWrite(LED_PIN, HIGH);
  digitalWrite(BUZZER_PIN, HIGH);
  delay(300);
  digitalWrite(BUZZER_PIN, LOW);

  // Spin servo forward
  Serial.println("🔄 Spinning servo...");
  dispenserServo.write(0);   // full speed CW for continuous servo
  delay(2000);

  dispenserServo.write(90);  // Stop
  Serial.println("🛑 Servo stopped.");

  for (int i = 0; i < 3; i++) {
    digitalWrite(LED_PIN, HIGH);
    delay(200);
    digitalWrite(LED_PIN, LOW);
    delay(200);
  }

  Serial.println("✅ Done!");
}

void loop() {
  WiFiClient client = server.available();

  if (!client) return;

  Serial.println("📩 New client connected");

  String req = client.readStringUntil('\r');
  client.flush();
  Serial.print("🔍 Request: ");
  Serial.println(req);

  if (req.indexOf("/vend?token=1") != -1) {
    dispenseItem();
  }

  // Respond
  client.println("HTTP/1.1 200 OK");
  client.println("Content-Type: text/plain");
  client.println("Connection: close");
  client.println();
  client.println("OK");

  client.stop();
  Serial.println("📤 Response sent.\n");
}
