/*
 * Smart Campus Delivery Robot — obstacle avoidance (HC-SR04 + L298N)
 * Pins: Trig 9, Echo 10, Motor1 2/3, Motor2 4/5
 */
#define trigPin 9
#define echoPin 10

#define motor1A 2
#define motor1B 3
#define motor2A 4
#define motor2B 5

long duration;
int distance;

void setup() {
  pinMode(trigPin, OUTPUT);
  pinMode(echoPin, INPUT);

  pinMode(motor1A, OUTPUT);
  pinMode(motor1B, OUTPUT);
  pinMode(motor2A, OUTPUT);
  pinMode(motor2B, OUTPUT);

  Serial.begin(9600);
}

int getDistance() {
  digitalWrite(trigPin, LOW);
  delayMicroseconds(2);

  digitalWrite(trigPin, HIGH);
  delayMicroseconds(10);
  digitalWrite(trigPin, LOW);

  duration = pulseIn(echoPin, HIGH, 30000UL);
  if (duration == 0) {
    return 999;
  }
  return (int)(duration * 0.034 / 2);
}

void moveForward() {
  digitalWrite(motor1A, HIGH);
  digitalWrite(motor1B, LOW);
  digitalWrite(motor2A, HIGH);
  digitalWrite(motor2B, LOW);
}

void stopRobot() {
  digitalWrite(motor1A, LOW);
  digitalWrite(motor1B, LOW);
  digitalWrite(motor2A, LOW);
  digitalWrite(motor2B, LOW);
}

void turnRight() {
  digitalWrite(motor1A, HIGH);
  digitalWrite(motor1B, LOW);
  digitalWrite(motor2A, LOW);
  digitalWrite(motor2B, HIGH);
}

void loop() {
  distance = getDistance();
  Serial.println(distance);

  if (distance < 20) {
    stopRobot();
    delay(500);
    turnRight();
    delay(700);
    stopRobot();
  } else {
    moveForward();
  }

  delay(100);
}
