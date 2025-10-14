/*--------------------*/
/*     USERS TABLE    */
/*--------------------*/

CREATE TABLE users (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(255) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


/*-----------------------*/
/*     MEAL LOGS TABLE   */
/*-----------------------*/

CREATE TABLE meal_logs (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id INT UNSIGNED NOT NULL,
  meal_name VARCHAR(150) NOT NULL DEFAULT 'Unnamed meal'
  eaten_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  experience BOOLEAN NOT NULL DEFAULT 0,   -- 0 = good, 1 = bad
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

  CONSTRAINT fk_meal_logs_user
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,

  INDEX idx_user_time (user_id, eaten_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


/*-----------------------*/
/*     CATEGORIES TABLE  */
/*-----------------------*/

CREATE TABLE categories (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL UNIQUE,
  description VARCHAR(255) NULL,
  is_active BOOLEAN NOT NULL DEFAULT 1)
  ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


/*------------------------------------------------*/
/*  JUNCTION TABLE: MEALS ↔ CATEGORIES            */
/*------------------------------------------------*/

CREATE TABLE meal_log_categories (
  meal_log_id INT UNSIGNED NOT NULL,
  category_id INT UNSIGNED NOT NULL,
  PRIMARY KEY (meal_log_id, category_id),

  CONSTRAINT fk_mlc_meal FOREIGN KEY (meal_log_id)
    REFERENCES meal_logs(id) ON DELETE CASCADE,
  CONSTRAINT fk_mlc_category FOREIGN KEY (category_id)
    REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


/*-----------------------------*/
/*     RESEARCH ARTICLES TABLE */
/*-----------------------------*/

CREATE TABLE research_articles (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  url VARCHAR(500) NOT NULL,
  summary TEXT NULL,
  category_id INT UNSIGNED NOT NULL,   -- link each article to a category
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,

  CONSTRAINT fk_article_category
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
