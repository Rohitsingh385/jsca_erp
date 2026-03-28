-- Add U15, U23, Open to tournaments age_category
ALTER TABLE tournaments MODIFY COLUMN age_category ENUM('U14','U15','U16','U19','U23','Senior','Open','Masters','Women') NOT NULL;

-- Add U15, U23, Open to players age_category too for consistency
ALTER TABLE players MODIFY COLUMN age_category ENUM('U14','U15','U16','U19','U23','Senior','Open','Masters') NOT NULL;
