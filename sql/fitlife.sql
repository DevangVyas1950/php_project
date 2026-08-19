-- ============================================
-- FitLife Database Schema + Seed Data
-- ============================================

CREATE DATABASE IF NOT EXISTS fitlife;
USE fitlife;

CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  age INT,
  gender ENUM('male','female','other'),
  weight_kg DECIMAL(5,2),
  height_cm DECIMAL(5,2),
  goal ENUM('lose_weight','gain_muscle','stay_healthy') DEFAULT 'stay_healthy',
  bmi DECIMAL(4,2),
  role ENUM('user','admin') DEFAULT 'user',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE foods (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  calories_per_100g DECIMAL(6,2),
  protein_g DECIMAL(5,2),
  carbs_g DECIMAL(5,2),
  fat_g DECIMAL(5,2),
  category ENUM('breakfast','lunch','dinner','snack')
);

CREATE TABLE diet_charts (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  goal ENUM('lose_weight','gain_muscle','stay_healthy'),
  total_calories INT,
  description TEXT,
  created_by INT,
  FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE diet_chart_meals (
  id INT AUTO_INCREMENT PRIMARY KEY,
  chart_id INT NOT NULL,
  food_id INT NOT NULL,
  meal_time ENUM('breakfast','lunch','dinner','snack'),
  quantity_g DECIMAL(6,2),
  FOREIGN KEY (chart_id) REFERENCES diet_charts(id) ON DELETE CASCADE,
  FOREIGN KEY (food_id) REFERENCES foods(id) ON DELETE CASCADE
);

CREATE TABLE user_diet_assignments (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  chart_id INT NOT NULL,
  assigned_date DATE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (chart_id) REFERENCES diet_charts(id) ON DELETE CASCADE
);

CREATE TABLE exercises (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(150) NOT NULL,
  category ENUM('cardio','strength','flexibility','yoga'),
  duration_min INT,
  calories_burned INT,
  instructions TEXT,
  difficulty ENUM('beginner','intermediate','advanced')
);

CREATE TABLE exercise_library (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(200) NOT NULL,
  category ENUM('cardio','yoga','strength','flexibility') NOT NULL,
  calories_per_min DECIMAL(5,2) DEFAULT 0.00,
  media_url VARCHAR(255) DEFAULT NULL,
  instructions TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE user_exercise_log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  exercise_id INT NOT NULL,
  category ENUM('cardio','yoga','strength','flexibility') NOT NULL,
  duration_mins INT NOT NULL,
  calories_burned INT NOT NULL,
  log_date DATE NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (exercise_id) REFERENCES exercise_library(id) ON DELETE CASCADE
);

CREATE TABLE tasks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  task_title VARCHAR(200) NOT NULL,
  task_type ENUM('diet','workout','water','general','other') DEFAULT 'general',
  due_date DATE,
  is_completed TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE user_tasks (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  task_title VARCHAR(200),
  task_type ENUM('diet','exercise','water','sleep','other'),
  due_date DATE,
  is_completed TINYINT(1) DEFAULT 0,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE food_log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  food_name VARCHAR(150) NOT NULL,
  meal_type ENUM('Breakfast','Lunch','Dinner','Snack') NOT NULL DEFAULT 'Breakfast',
  calories DECIMAL(6,2) NOT NULL,
  protein_g DECIMAL(6,2) DEFAULT 0,
  carbs_g DECIMAL(6,2) DEFAULT 0,
  fat_g DECIMAL(6,2) DEFAULT 0,
  logged_date DATE NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE exercise_logs (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  exercise_id INT NOT NULL,
  duration_min INT,
  calories_burned INT,
  log_date DATE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
  FOREIGN KEY (exercise_id) REFERENCES exercises(id) ON DELETE CASCADE
);

CREATE TABLE progress_log (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  weight_kg DECIMAL(5,2),
  bmi DECIMAL(4,2),
  log_date DATE,
  notes TEXT,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE weight_history (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  weight_kg DECIMAL(5,2) NOT NULL,
  recorded_date DATE NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ============================================
-- SEED DATA
-- ============================================

-- Admin + demo users (password for all: password)
INSERT INTO users (name, email, password, age, gender, weight_kg, height_cm, goal, bmi, role) VALUES
('Admin', 'admin@fitlife.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 30, 'male', 70, 175, 'stay_healthy', 22.86, 'admin'),
('Rahul Mehta', 'rahul@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 25, 'male', 85, 172, 'lose_weight', 28.74, 'user'),
('Priya Sharma', 'priya@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 22, 'female', 58, 162, 'stay_healthy', 22.10, 'user');

INSERT INTO foods (name, calories_per_100g, protein_g, carbs_g, fat_g, category) VALUES
('Oatmeal', 71, 2.5, 12.0, 1.5, 'breakfast'),
('Boiled Eggs', 155, 13.0, 1.1, 11.0, 'breakfast'),
('Banana', 89, 1.1, 23.0, 0.3, 'breakfast'),
('Brown Rice', 111, 2.6, 23.0, 0.9, 'lunch'),
('Grilled Chicken Breast', 165, 31.0, 0.0, 3.6, 'lunch'),
('Dal (Lentils)', 116, 9.0, 20.0, 0.4, 'lunch'),
('Mixed Vegetable Salad', 45, 2.0, 9.0, 0.5, 'lunch'),
('Roti (Wheat)', 264, 8.0, 56.0, 1.0, 'dinner'),
('Paneer (Cottage Cheese)', 265, 18.0, 1.2, 20.0, 'dinner'),
('Idli', 39, 2.0, 8.0, 0.1, 'dinner'),
('Almonds', 579, 21.0, 22.0, 50.0, 'snack'),
('Greek Yogurt', 59, 10.0, 3.6, 0.4, 'snack'),
('Apple', 52, 0.3, 14.0, 0.2, 'snack'),
('Whey Protein Shake', 120, 24.0, 3.0, 2.0, 'snack'),
('Sweet Potato', 86, 1.6, 20.0, 0.1, 'lunch');

INSERT INTO exercises (name, category, duration_min, calories_burned, instructions, difficulty) VALUES
('Morning Walk', 'cardio', 30, 120, 'Walk at a brisk pace for 30 minutes. Keep your back straight and arms swinging naturally.', 'beginner'),
('Jogging', 'cardio', 30, 250, 'Jog at a steady pace. Breathe rhythmically. Maintain a pace where you can still talk.', 'beginner'),
('Jump Rope', 'cardio', 15, 180, 'Jump continuously for 1 min, rest 30 sec. Repeat 5 times. Keep elbows close to body.', 'intermediate'),
('Push-Ups', 'strength', 15, 80, 'Keep body straight, lower chest to floor. Do 3 sets of 10-15 reps with 60 sec rest.', 'beginner'),
('Squats', 'strength', 20, 100, 'Feet shoulder-width apart. Lower until thighs parallel to floor. Do 3 sets of 15 reps.', 'beginner'),
('Plank', 'strength', 10, 50, 'Hold plank position for 30-60 seconds. Keep hips level. Do 3 sets.', 'intermediate'),
('Dumbbell Bicep Curl', 'strength', 20, 90, 'Curl dumbbells to shoulder height. Control the movement down. 3 sets of 12 reps.', 'intermediate'),
('Burpees', 'cardio', 15, 200, 'Stand, squat, jump back to plank, push-up, jump forward, jump up. 3 sets of 10.', 'advanced'),
('Yoga Sun Salutation', 'yoga', 20, 60, 'Flow through 5 rounds of Surya Namaskar. Breathe deeply through each pose.', 'beginner'),
('Cycling', 'cardio', 45, 300, 'Cycle at moderate intensity. Keep cadence around 80-90 RPM. Stay hydrated.', 'intermediate'),
('Deadlift', 'strength', 30, 150, 'Keep back straight, hinge at hips, lift bar to hip height. 3 sets of 8 reps.', 'advanced'),
('Stretching Routine', 'flexibility', 15, 30, 'Hold each stretch for 20-30 seconds. Focus on major muscle groups. Do not bounce.', 'beginner');

INSERT INTO diet_charts (title, goal, total_calories, description, created_by) VALUES
('Weight Loss Plan - 1500 kcal', 'lose_weight', 1500, 'A balanced low-calorie plan with high protein to preserve muscle while losing fat.', 1),
('Muscle Gain Plan - 2500 kcal', 'gain_muscle', 2500, 'High protein, moderate carbs plan to support muscle building with adequate energy.', 1),
('Healthy Maintenance - 2000 kcal', 'stay_healthy', 2000, 'A balanced everyday plan with all macronutrients for general health and wellbeing.', 1);

INSERT INTO diet_chart_meals (chart_id, food_id, meal_time, quantity_g) VALUES
(1,1,'breakfast',80),(1,2,'breakfast',100),(1,3,'breakfast',100),
(1,4,'lunch',100),(1,5,'lunch',150),(1,7,'lunch',150),
(1,8,'dinner',60),(1,6,'dinner',100),
(1,13,'snack',100),(1,12,'snack',100),
(2,1,'breakfast',120),(2,2,'breakfast',200),(2,14,'breakfast',250),
(2,4,'lunch',200),(2,5,'lunch',200),
(2,9,'dinner',150),(2,8,'dinner',120),
(2,11,'snack',30),(2,14,'snack',250),
(3,1,'breakfast',100),(3,2,'breakfast',150),(3,3,'breakfast',100),
(3,4,'lunch',150),(3,5,'lunch',150),(3,7,'lunch',100),
(3,8,'dinner',90),(3,9,'dinner',100),
(3,13,'snack',150),(3,11,'snack',20);

INSERT INTO user_diet_assignments (user_id, chart_id, assigned_date) VALUES
(2, 1, CURDATE()), (3, 3, CURDATE());

INSERT INTO user_tasks (user_id, task_title, task_type, due_date) VALUES
(2,'Drink 8 glasses of water','water',CURDATE()),
(2,'Follow your diet chart meals','diet',CURDATE()),
(2,'Complete Morning Walk','exercise',CURDATE()),
(2,'Sleep 7-8 hours tonight','sleep',CURDATE()),
(3,'Drink 8 glasses of water','water',CURDATE()),
(3,'Follow your diet chart meals','diet',CURDATE()),
(3,'Complete Yoga Sun Salutation','exercise',CURDATE());
