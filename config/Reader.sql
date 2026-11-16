CREATE DATABASE IF NOT EXISTS Reader;

USE Reader;

CREATE TABLE IF NOT EXISTS users (
  user_id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) UNIQUE NOT NULL,
  email VARCHAR(100) UNIQUE NOT NULL,
  password VARCHAR(255) NOT NULL,
  profile_pic VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS novels (
  novel_id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(150) NOT NULL,
  author VARCHAR(100) NOT NULL,
  genre VARCHAR(100) DEFAULT NULL,
  description TEXT,
  cover_image VARCHAR(255) DEFAULT 'default.png',
  price DECIMAL(8,2) NOT NULL DEFAULT 0.00,
  rating DECIMAL(3,2) NOT NULL DEFAULT 0.00,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CHECK (price >= 0),
  CHECK (rating >= 0 AND rating <= 5)
);

CREATE TABLE IF NOT EXISTS novel_ratings (
  rating_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  novel_id INT NOT NULL,
  rating TINYINT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
  FOREIGN KEY (novel_id) REFERENCES novels(novel_id) ON DELETE CASCADE,
  UNIQUE KEY unique_user_novel_rating (user_id, novel_id),
  CHECK (rating >= 1 AND rating <= 5),
  INDEX idx_ratings_novel (novel_id)
);

CREATE TABLE IF NOT EXISTS chapters (
  chapter_id INT AUTO_INCREMENT PRIMARY KEY,
  novel_id INT NOT NULL,
  chapter_number INT NOT NULL,
  chapter_title VARCHAR(150),
  content LONGTEXT NOT NULL,
  FOREIGN KEY (novel_id) REFERENCES novels(novel_id) ON DELETE CASCADE,
  UNIQUE KEY unique_chapter (novel_id, chapter_number)
);

CREATE TABLE IF NOT EXISTS purchases (
  purchase_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  novel_id INT NOT NULL,
  amount DECIMAL(8,2) NOT NULL DEFAULT 0.00,
  purchased_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
  FOREIGN KEY (novel_id) REFERENCES novels(novel_id) ON DELETE CASCADE,
  UNIQUE KEY unique_purchase (user_id, novel_id)
);

CREATE TABLE IF NOT EXISTS payments (
  payment_id INT AUTO_INCREMENT PRIMARY KEY,
  purchase_id INT NOT NULL,
  user_id INT NOT NULL,
  novel_id INT NOT NULL,
  amount DECIMAL(8,2) NOT NULL,
  payment_method ENUM('UPI','CARD','NET_BANKING') NOT NULL,
  transaction_id VARCHAR(64) NOT NULL UNIQUE,
  payment_status ENUM('PAID','FAILED') NOT NULL DEFAULT 'PAID',
  paid_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (purchase_id) REFERENCES purchases(purchase_id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
  FOREIGN KEY (novel_id) REFERENCES novels(novel_id) ON DELETE CASCADE,
  INDEX idx_payments_user (user_id),
  INDEX idx_payments_novel (novel_id)
);

CREATE TABLE IF NOT EXISTS wishlist (
  wishlist_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  novel_id INT NOT NULL,
  added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
  FOREIGN KEY (novel_id) REFERENCES novels(novel_id) ON DELETE CASCADE,
  UNIQUE KEY unique_wish (user_id, novel_id)
);

CREATE TABLE IF NOT EXISTS reading_history (
  history_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  novel_id INT NOT NULL,
  chapter_id INT NOT NULL,
  last_read_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
  FOREIGN KEY (novel_id) REFERENCES novels(novel_id) ON DELETE CASCADE,
  FOREIGN KEY (chapter_id) REFERENCES chapters(chapter_id) ON DELETE CASCADE,
  UNIQUE KEY unique_progress (user_id, novel_id)
);

INSERT INTO chapters (novel_id, chapter_number, chapter_title, content)
SELECT n.novel_id, 1, 'The Orchard Gate', 'The gate had not opened in eleven years, and yet this morning it stood ajar...'
FROM novels n WHERE n.title='The Silent Orchard'
AND NOT EXISTS (SELECT 1 FROM chapters c WHERE c.novel_id=n.novel_id AND c.chapter_number=1);

INSERT INTO chapters (novel_id, chapter_number, chapter_title, content)
SELECT n.novel_id, 2, 'Old Roots', 'Beneath the oldest tree, Mira found something that should not have been there...'
FROM novels n WHERE n.title='The Silent Orchard'
AND NOT EXISTS (SELECT 1 FROM chapters c WHERE c.novel_id=n.novel_id AND c.chapter_number=2);

INSERT INTO chapters (novel_id, chapter_number, chapter_title, content)
SELECT n.novel_id, 1, 'The Last Forge', 'Smoke rose from a forge that had no right to still be burning...'
FROM novels n WHERE n.title='Iron & Ashes'
AND NOT EXISTS (SELECT 1 FROM chapters c WHERE c.novel_id=n.novel_id AND c.chapter_number=1);

INSERT INTO chapters (novel_id, chapter_number, chapter_title, content)
SELECT n.novel_id, 1, 'Unsent', 'Dear no one, I am writing this letter knowing you will never read it...'
FROM novels n WHERE n.title='Letters to No One'
AND NOT EXISTS (SELECT 1 FROM chapters c WHERE c.novel_id=n.novel_id AND c.chapter_number=1);

INSERT INTO novels (title, author, genre, description, cover_image, price, rating) VALUES
('Blue Lock','Muneyuki Kaneshiro','Sports','A competitive football manga about talented players fighting to become the greatest striker.','coverA.jpg',2.99,5),
(
'Jujutsu Kaisen',
'Gege Akutami',
'Action',
'A young student enters the world of cursed spirits and supernatural battles.',
'coverA.jpg',
3.99,
5
),

(
'Demon Slayer',
'Koyoharu Gotouge',
'Fantasy',
'A young demon slayer fights dangerous demons while searching for a way to restore his sister.',
'coverA.jpg',
2.99,
5
),

(
'Chainsaw Man',
'Tatsuki Fujimoto',
'Action',
'A poor devil hunter gains incredible powers after merging with his mysterious chainsaw companion.',
'coverA.jpg',
3.49,
4
),

(
'Tokyo Ghoul',
'Sui Ishida',
'Horror',
'A college student becomes half-ghoul and struggles to survive between humans and monsters.',
'coverA.jpg',
3.99,
5
),

(
'Death Note',
'Tsugumi Ohba',
'Mystery',
'A student discovers a supernatural notebook capable of killing anyone whose name is written inside it.',
'coverA.jpg',
2.49,
5
),

(
'One Punch Man',
'ONE',
'Action',
'A hero who can defeat almost any enemy with one punch searches for a real challenge.',
'coverA.jpg',
2.99,
4
),

(
'Haikyuu!!',
'Haruichi Furudate',
'Sports',
'A short but determined volleyball player dreams of becoming a great athlete.',
'coverA.jpg',
1.99,
5
),

(
'Attack on Titan',
'Hajime Isayama',
'Fantasy',
'Humanity fights for survival against gigantic creatures known as Titans.',
'coverA.jpg',
4.49,
5
),

(
'My Hero Academia',
'Kohei Horikoshi',
'Superhero',
'A powerless boy dreams of becoming a professional hero in a world where superpowers are common.',
'coverA.jpg',
2.99,
4
),

(
'Spy x Family',
'Tatsuya Endo',
'Comedy',
'A spy, an assassin and a telepath create a fake family while hiding their unusual secrets.',
'coverA.jpg',
2.49,
5
),

(
'Tokyo Revengers',
'Ken Wakui',
'Action',
'A young man travels back in time to change the fate of people connected to a dangerous gang.',
'coverA.jpg',
3.49,
4
),

(
'Dr. Stone',
'Riichiro Inagaki',
'Science Fiction',
'After humanity is mysteriously petrified, a genius teenager attempts to rebuild civilization using science.',
'coverA.jpg',
2.99,
4
),

(
'Black Clover',
'Yuki Tabata',
'Fantasy',
'A magicless orphan works toward becoming the greatest magic knight in a world ruled by magic.',
'coverA.jpg',
2.99,
4
),

(
'Mashle',
'Hajime Komoto',
'Comedy',
'A boy without magic relies entirely on physical strength in a magical society.',
'coverA.jpg',
1.99,
4
),


(
'Solo Leveling',
'Chugong',
'Fantasy',      
'The weakest hunter gains a mysterious system that allows him to become stronger without limits.',
'coverA.jpg',
4.99,
5
),

(
'Omniscient Reader',
'Sing Shong',
'Fantasy',      
'A reader discovers that the novel he finished reading has become reality and only he knows how the story ends.',
'coverA.jpg',
3.99,
5
),

(
'Tower of God',
'SIU',
'Fantasy',      
'A mysterious tower offers unimaginable rewards to those brave enough to climb it.',
'coverA.jpg',
3.49,
5
),

(
'The Beginning After the End',
'TurtleMe',
'Fantasy',      
'A powerful king is reincarnated into a magical world and receives another chance at life.',
'coverA.jpg',
4.49,
5
),

(
'Lookism',
'Park Tae-jun',
'Drama',      
'A bullied student discovers he can switch between two completely different bodies.',
'coverA.jpg',
2.99,
4
),

(
'The God of High School',
'Yongje Park',
'Action',      
'Martial artists from across Korea compete in a mysterious tournament with powerful supernatural secrets.',
'coverA.jpg',
3.49,
4
),

('Noblesse','Son Je-ho','Fantasy',      'An ancient vampire awakens after centuries and discovers how much the modern world has changed.','coverA.jpg',2.99,4),
('Eleceed','Son Je-ho','Action',      'A kind-hearted teenager with extraordinary speed meets a powerful awakened being trapped inside a cat.','coverA.jpg',3.99,5),
('Mercenary Enrollment','YC','Action',      'A teenage survivor of a plane crash returns home after years of training as a mercenary.','coverA.jpg',3.49,5),
('Nano Machine','Han-Joong-Wol-Ya','Action',      'A young martial artist receives advanced technology from the future that transforms his abilities.','coverA.jpg',4.99,5),
('Return of the Mount Hua Sect','Biga','Action',      'A legendary martial artist is reborn years after the destruction of his sect and begins rebuilding it.','coverA.jpg',3.99,5),
('The Greatest Estate Developer','BK_Moon','Comedy','A clever engineering student wakes up as a lazy nobleman and uses modern knowledge to save his territory.''coverA.jpg',2.99,5),
('Mercenary Enrollment','YC','Drama','A highly trained young mercenary tries to live an ordinary school life after returning to his family.','coverA.jpg',2.99,4),
('Weak Hero','SEOPASS','Action','A physically weak student uses intelligence and strategy to survive against violent bullies.','coverA.jpg',2.49,5),
('The Boxer','JH','Sports','A mysterious young fighter enters the world of professional boxing and discovers his extraordinary talent.','coverA.jpg',3.49,5);

INSERT INTO `novels`(`title`,`author`,`genre`,`description`,`cover_image`,`price`,`rating`) VALUES
('Fullmetal Alchemist','Hiromu Arakawa','Adventure','Two brothers use alchemy to search for the Philosopher''s Stone after a forbidden ritual costs them their bodies.','cover31.jpg',3.99,5),
('Steins`Gate','5pb. & Nitroplus','Science Fiction','A self-proclaimed mad scientist discovers a way to send messages to the past and must prevent a dystopian future.','cover32.jpg',3.49,5),
('Code Geass','Ichirō Ōkouchi','Mecha','An exiled prince gains the power of absolute command and leads a rebellion against a vast empire.','cover33.jpg',3.99,5),
('Cowboy Bebop','Shinichirō Watanabe','Space Western','A ragtag crew of bounty hunters drifts through space chasing criminals while running from their pasts.','cover34.jpg',2.99,5),
('Monster','Naoki Urasawa','Psychological Thriller','A brilliant surgeon saves a boy who grows up to become a terrifying serial killer, forcing him to hunt him down.','cover35.jpg',4.49,5),
('Vagabond','Takehiko Inoue','Historical','A legendary swordsman seeks enlightenment and the true meaning of strength through countless duels.','cover36.jpg',3.99,5),
('20th Century Boys','Naoki Urasawa','Mystery','A group of childhood friends reunite to stop a cult leader fulfilling a apocalyptic prophecy they invented as kids.','cover37.jpg',4.49,5)
('Bleach','Tite Kubo','Supernatural','A teenager gains the powers of a Soul Reaper and must protect the living from evil spirits.','cover38.jpg',2.99,4),
('Fairy Tail','Hiro Mashima','Fantasy','A rambunctious wizard guild takes on dangerous jobs while protecting each other like family.','cover39.jpg',2.49,4),
('Hunter x Hunter','Yoshihiro Togashi','Adventure','A boy sets out to become a Hunter and find his missing father in a world of deadly trials and strange powers.','cover40.jpg',3.49,5),
('Solo Leveling','Chugong','Action Fantasy','The world''s weakest hunter gains a mysterious system that lets him level up without limit.','cover41.jpg',3.99,5),
('The Remarried Empress','Alphatart','Romance Fantasy','A betrayed empress divorces her cheating husband and remarries a powerful emperor, reshaping the empire.','cover42.jpg',2.99,4),
('Who Made Me a Princess','Plutus','Romance Fantasy','A girl reincarnated as a doomed princess must win over her cold father to survive her tragic fate.','cover43.jpg',2.49,4),
('Villains Are Destined to Die','Gwon Gyeoeul','Romance Fantasy','Trapped in a hard-mode otome game, a player must survive as the villainess and escape death flags.','cover44.jpg',2.99,4),
('I Shall Master This Family','Kim Roeg','Romance Fantasy','A reincarnated heiress uses her knowledge of the future to save her family from ruin.','cover45.jpg',3.49,4),
('1984','George Orwell','Dystopian','In a totalitarian future, one man dares to rebel against a regime that controls every thought.','cover46.jpg',1.99,5),
('Animal Farm','George Orwell','Satire','Farm animals overthrow their human master only to find their new leaders just as tyrannical.','cover47.jpg',1.49,5),
('Brave New World','Aldous Huxley','Dystopian','A society engineered for pleasure and stability hides a chilling cost to human freedom.','cover48.jpg',2.49,4),
('Fahrenheit 451','Ray Bradbury','Dystopian','A fireman who burns books begins to question the world he has helped to build.','cover49.jpg',2.49,4),
('The Great Gatsby','F. Scott Fitzgerald','Drama','A mysterious millionaire pursues a lost love amid the glittering excess of the Jazz Age.','cover50.jpg',1.99,4),
('Pride and Prejudice','Jane Austen','Romance','A sharp-witted young woman navigates courtship, class, and pride in Regency England.','cover51.jpg',1.49,5),
('The Hobbit','J.R.R. Tolkien','Fantasy','A reluctant hobbit joins a company of dwarves on a quest to reclaim a mountain from a dragon.','cover52.jpg',2.99,5),
('Dune','Frank Herbert','Science Fiction','A young heir becomes entangled in a desert planet''s deadly politics and mystical destiny.','cover53.jpg',4.49,5),
('Foundation','Isaac Asimov','Science Fiction','A mathematician predicts the fall of a galactic empire and plants seeds for its successor.','cover54.jpg',3.49,5),
('Ender''s Game','Orson Scott Card','Science Fiction','A gifted child is trained in a war game that may determine humanity''s survival.','cover55.jpg',3.49,5),
('The Name of the Wind','Patrick Rothfuss','Fantasy','A legendary magician recounts his rise from orphan to the most notorious wizard of his age.','cover56.jpg',4.99,5),
('Mistborn','Brandon Sanderson','Fantasy','A street thief discovers she can burn metals for magical power and joins a rebellion against an immortal tyrant.','cover57.jpg',3.99,5),
('The Way of Kings','Brandon Sanderson','Epic Fantasy','On a world of storms, ancient oaths resurface as warring factions face a renewed threat.','cover58.jpg',4.99,5),
('A Game of Thrones','George R.R. Martin','Epic Fantasy','Noble houses scheme for a throne while an ancient winter creeps toward the realm.','cover59.jpg',4.99,5),
('The Gunslinger','Stephen King','Dark Fantasy','A lone gunslinger pursues a mysterious man in black across a dying, desolate world.','cover60.jpg',3.49,4),
('Murder on the Orient Express','Agatha Christie','Mystery','A detective must solve a murder aboard a snowbound train where everyone is a suspect.','cover61.jpg',1.99,5),
('The Silent Patient','Alex Michaelides','Psychological Thriller','A woman stops speaking after murdering her husband, and a therapist becomes obsessed with her.','cover62.jpg',2.99,4),
('Gone Girl','Gillian Flynn','Thriller','A marriage unravels when a woman disappears and her husband becomes the prime suspect.','cover63.jpg',2.99,5),
('The Shadow of the Wind','Carlos Ruiz Zafón','Mystery','A boy''s discovery of a forgotten novel leads him into a labyrinth of secrets in postwar Barcelona.','cover64.jpg',3.49,5),
('The Count of Monte Cristo','Alexandre Dumas','Adventure','A wrongfully imprisoned man escapes to seek elaborate revenge on those who betrayed him.','cover65.jpg',2.49,5),
('Re:Zero','Tappei Nagatsuki','Isekai','A young man transported to a fantasy world finds he dies and rewinds time after each death.','cover67.jpg',3.99,5),
('That Time I Got Reincarnated as a Slime','Fuse','Isekai','A slain office worker is reborn as a slime and builds a nation of monsters.','cover68.jpg',2.49,4),
('Log Horizon','Mamare Touno','Isekai','Players trapped in an MMORPG must build a real society from the game''s mechanics.','cover69.jpg',2.99,4),
('Shadow Slave','Guiltythree','Dark Fantasy','A destitute boy enters a nightmare realm and gains a cursed power that could save or destroy him.','cover70.jpg',4.49,5),
('The Primal Hunter','Zogarth','LitRPG','Humanity is initiated into a monstrous system where a solitary hunter rises through ruthless trials.','cover71.jpg',3.49,4),
('He Who Fights With Monsters','Shirtaloon','LitRPG','An ordinary man is summoned to a magical world and forced to grow powerful to survive.','cover72.jpg',3.99,4),
('Warlock of the Magus World','The Plagiarist','Dark Fantasy','A ruthless transmigrator claws his way through a brutal world of magic and survival.','cover73.jpg',3.49,4),
('I''m the Max-Level Newbie','Wan-Sung','Action','A low-level player suddenly maxes out and must protect the world from a game turned real.','cover74.jpg',2.99,4),
('Regressor Instruction Manual','Heesoo','Dark Fantasy','A regressor uses his knowledge of the future—and a careful mask—to manipulate everyone around him.','cover75.jpg',3.49,4),
('SSS-Class Suicide Hunter','Shin Noah','Action Fantasy','A hunter gains the power to return after death—but must sacrifice himself to grow stronger.','cover76.jpg',3.99,5),
('Eleceed','Son Jeho & ZHENA','Action Comedy','A kind-hearted boy with hidden powers befriends a fat cat who is secretly a legendary fighter.','cover77.jpg',2.99,5),
('The Boxer','JH','Sports Drama','A gifted but emotionally empty boy is drawn into the brutal world of professional boxing.','cover78.jpg',3.49,5),
('Wind Breaker','Satoru Nii','Action Drama','A delinquent joins a gang of bikers who protect their town and discovers a new purpose.','cover79.jpg',2.49,4),
('Blue Period','Tsubasa Yamaguchi','Drama','A high-achieving student discovers a passion for art and chases admission to art school.','cover80.jpg',2.49,5);