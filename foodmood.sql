/*--------------------*/
/*     User data      */
/*--------------------*/

CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

/*-----------------------*/
/*     User meal log     */
/*-----------------------*/

CREATE TABLE meal_logs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  meal_name VARCHAR(150) NULL,        
  eaten_at DATETIME NOT NULL,

  -- Experience: 0 = good, 1 = bad (default good)
  experience TINYINT(1) NOT NULL DEFAULT 0,

  dairy TINYINT(1) NOT NULL DEFAULT 0,
  gluten TINYINT(1) NOT NULL DEFAULT 0,
  legumes TINYINT(1) NOT NULL DEFAULT 0,
  cruciferous_vegetables TINYINT(1) NOT NULL DEFAULT 0,
  alliums TINYINT(1) NOT NULL DEFAULT 0,
  fruits TINYINT(1) NOT NULL DEFAULT 0,
  sugar_alcohols_artificial_sweeteners TINYINT(1) NOT NULL DEFAULT 0,
  high_fat_fried TINYINT(1) NOT NULL DEFAULT 0,
  spicy TINYINT(1) NOT NULL DEFAULT 0,
  acidic TINYINT(1) NOT NULL DEFAULT 0,
  caffeine TINYINT(1) NOT NULL DEFAULT 0,
  alcohol TINYINT(1) NOT NULL DEFAULT 0,
  processed_food TINYINT(1) NOT NULL DEFAULT 0,

  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

  --  user relation
  CONSTRAINT fk_meal_logs_user
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,

  -- Speeds up queries with index and support safe inserts and char like emojis
  INDEX idx_user_time (user_id, eaten_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


/*-----------------------*/
/*    Article Archive    */
/*-----------------------*/

CREATE TABLE research_articles (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  url VARCHAR(500) NOT NULL,
  summary TEXT NULL,               -- Summary text
  category VARCHAR(100) NOT NULL,  -- add which categories are included in the article
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;