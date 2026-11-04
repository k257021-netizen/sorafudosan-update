-- Sora Living database setup and sample data.
-- Create an administrator separately using a password_hash() generated hash.

CREATE DATABASE IF NOT EXISTS `sora` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `sora`;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET NAMES utf8mb4;

CREATE TABLE `inquiries` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `property_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `phone` varchar(30) DEFAULT NULL,
  `message` text NOT NULL,
  `is_read` tinyint(1) NOT NULL DEFAULT 0,
  `status` varchar(20) NOT NULL DEFAULT 'new',
  `admin_note` text DEFAULT NULL,
  `privacy_accepted_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `site_documents` (
  `document_key` varchar(32) NOT NULL,
  `content` mediumtext NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`document_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `site_documents` (`document_key`, `content`) VALUES
('terms', '本規約は、Sora Livingの物件検索、会員機能、お問い合わせ機能の利用条件を定めます。\n\n## 利用時のお願い\n利用者は正確な情報を登録し、第三者へのなりすまし、不正アクセス、サービス運営を妨げる行為を行わないものとします。\n\n## 掲載情報について\n掲載情報や写真は参考情報です。募集状況、費用、設備等は変わる場合があるため、契約前にお問い合わせのうえご確認ください。サンプル物件は架空の情報です。\n\n## サービスの変更\n保守や運営上の必要に応じ、機能や掲載内容を変更・停止することがあります。\n\n制定日：2026年10月21日'),
('privacy', 'Sora Living（以下「当サイト」）は、アカウント登録、お気に入り保存、お問い合わせ対応のために、氏名、メールアドレス、電話番号、お問い合わせ内容を取得します。\n\n## 利用目的\n物件探し機能の提供、本人確認、問い合わせへの回答、サービス改善および不正利用防止に利用します。アカウントのセキュリティを守るため、パスワード変更の日時、接続元IPアドレス、ブラウザー情報を記録し、セキュリティ記録は作成から180日後に削除します。パスワードを変更または再設定した場合、登録メールアドレスに通知します。\n\n## 第三者提供・保管\n法令に基づく場合を除き、本人の同意なく個人情報を第三者へ提供しません。利用目的に必要な期間、安全管理に配慮して保管し、不要になった情報は適切に削除します。\n\n## お問い合わせ・開示請求\n個人情報に関する相談や開示・訂正・削除の希望は、サイトのお問い合わせフォームからご連絡ください。\n\n制定日：2026年11月2日');

CREATE TABLE `properties` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `station` varchar(50) DEFAULT NULL,
  `area` varchar(50) DEFAULT NULL,
  `school` varchar(50) DEFAULT NULL,
  `transaction_type` varchar(10) NOT NULL DEFAULT 'rent',
  `rent` int(11) DEFAULT NULL,
  `sale_price` bigint(20) DEFAULT NULL,
  `management_fee` int(11) DEFAULT NULL,
  `deposit_amount` int(11) DEFAULT NULL,
  `key_money_amount` int(11) DEFAULT NULL,
  `pet_allowed` tinyint(1) NOT NULL DEFAULT 0,
  `layout` varchar(20) NOT NULL DEFAULT '1K',
  `area_sqm` decimal(6,2) DEFAULT NULL,
  `floor_number` smallint DEFAULT NULL,
  `initial_cost` int(11) DEFAULT NULL,
  `status` varchar(20) NOT NULL DEFAULT 'available',
  `description` text DEFAULT NULL,
  `image` varchar(200) DEFAULT 'images/property1.jpg',
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `properties` (`id`, `name`, `station`, `area`, `school`, `rent`, `description`, `image`) VALUES
(1, 'サンプルマンション', '横浜駅', '横浜市', 'yokohama', 60000, '【架空のサンプル物件】駅に近く、通学にも便利なお部屋です。横浜駅　歩行　5分', 'images/property1.jpg'),
(2, 'さくらハイツ', '桜木町駅', '横浜市', 'yokohama', 55000, '【架空のサンプル物件】静かな住宅街にある落ち着いたお部屋です。桜木町駅　歩行　8分　　関内駅　15分', 'images/property2.jpg'),
(3, '川崎レジデンス', '川崎駅', '川崎市', 'kanagawa', 65000, '【架空のサンプル物件】新築、オートロック付きのお部屋です。川崎駅　歩行　5分', 'images/property3.jpg'),
(4, 'レオパレス24-12', '川崎駅', '川崎市', '令和日本語学院', 80000, '【架空のサンプル物件】学校への通学に便利な物件です。川崎駅　歩行　4分　　蒲田駅　歩行　10分', 'images/property4.jpg'),
(5, '横浜みなとレジデンス', '横浜駅', '西区', '神奈川大学（みなとみらいキャンパス）', 88000, '【架空のサンプル物件】横浜駅やみなとみらい方面へ移動しやすい想定の部屋です。横浜駅　歩行　7分　　神奈川大学　歩行　10分', 'images/property1.jpg'),
(6, '白楽グリーンコート', '白楽駅', '神奈川区', '神奈川大学（横浜キャンパス）', 62000, '【架空のサンプル物件】白楽駅周辺を想定した学生向けの部屋です。白楽駅　歩行　6分　　神奈川大学　8分', 'images/property2.jpg'),
(7, '常盤台テラス', '和田町駅', '保土ケ谷区', '横浜国立大学', 58000, '【架空のサンプル物件】常盤台方面へ通う学生を想定した部屋です。和田町駅　歩行　8分　　横浜駅　歩行　25分　横浜国立大学　歩行　5分', 'images/property3.jpg'),
(8, '金沢八景シーサイド', '金沢八景駅', '金沢区', '横浜市立大学（金沢八景キャンパス）', 57000, '【架空のサンプル物件】金沢八景駅周辺を想定したコンパクトな部屋です。金沢八景駅　歩行　10分　　横浜市立大学　歩行　8分', 'images/property4.jpg'),
(9, '新横浜パークヒル', '新横浜駅', '港北区', 'yokohama', 72000, '【架空のサンプル物件】新幹線や市営地下鉄を利用しやすい想定の部屋です。新横浜駅　歩行　5分　　菊名駅　10分', 'images/property1.jpg'),
(10, '鶴見リバーコート', '鶴見駅', '鶴見区', 'kanagawa', 59000, '【架空のサンプル物件】鶴見駅周辺の生活を想定した部屋です。鶴見駅　歩行　12分', 'images/property2.jpg'),
(11, '関内シティハイツ', '関内駅', '中区', '令和日本語学院', 69000, '【架空のサンプル物件】関内駅周辺を想定した通学向けの部屋です。関内駅　歩行　5分　桜木町駅　歩行　１２分', 'images/property3.jpg'),
(12, '川崎駅前ブリーズ', '川崎駅', '川崎区', 'kanagawa', 76000, '【架空のサンプル物件】川崎駅周辺の買い物や通勤に便利な想定の部屋です。川崎駅　歩行　３分', 'images/property4.jpg'),
(13, '武蔵小杉セントラル', '武蔵小杉駅', '中原区', 'kanagawa', 82000, '【架空のサンプル物件】複数路線を使う暮らしを想定した部屋です。', 'images/property1.jpg'),
(14, '溝の口ミュージックハイツ', '溝の口駅', '高津区', '洗足学園音楽大学', 67000, '【架空のサンプル物件】溝の口駅周辺を想定した学生向けの部屋です。', 'images/property2.jpg'),
(15, '向ヶ丘遊園メゾン', '向ヶ丘遊園駅', '多摩区', '専修大学（生田キャンパス）', 59000, '【架空のサンプル物件】向ヶ丘遊園駅周辺を想定した学生向けの部屋です。', 'images/property3.jpg'),
(16, '新百合ヶ丘グリーンコート', '新百合ヶ丘駅', '麻生区', 'kanagawa', 64000, '【架空のサンプル物件】新百合ヶ丘駅周辺を想定した静かな部屋です。', 'images/property4.jpg'),
(17, '戸塚サンリーフ', '戸塚駅', '戸塚区', 'yokohama', 62000, '【架空のサンプル物件】戸塚駅周辺の生活を想定した部屋です。', 'images/property1.jpg'),
(18, '鎌倉小町レジデンス', '鎌倉駅', '鎌倉市', 'kanagawa', 81000, '【架空のサンプル物件】鎌倉駅周辺を想定した落ち着いた部屋です。', 'images/property2.jpg'),
(19, '藤沢サンライト', '藤沢駅', '藤沢市', 'kanagawa', 70000, '【架空のサンプル物件】藤沢駅周辺の暮らしを想定した部屋です。', 'images/property3.jpg'),
(20, '横須賀ベイサイド', '横須賀中央駅', '横須賀市', 'kanagawa', 64000, '【架空のサンプル物件】横須賀中央駅周辺を想定した部屋です。', 'images/property4.jpg');

INSERT INTO `properties` (`name`, `station`, `area`, `transaction_type`, `sale_price`, `description`, `image`)
VALUES ('横浜サンプル戸建（売買）', '横浜駅', '西区', 'sale', 49800000, '【架空のサンプル物件】売買機能の表示確認用に作成した参考物件です。', 'images/demo-room-5.jpg');

INSERT INTO `properties` (`name`, `station`, `area`, `school`, `rent`, `description`, `image`)
SELECT samples.`name`, samples.`station`, samples.`area`, samples.`school`, samples.`rent`, samples.`description`, samples.`image`
FROM (
    SELECT '横浜みなとレジデンス' AS `name`, '横浜駅' AS `station`, '西区' AS `area`, '神奈川大学（みなとみらいキャンパス）' AS `school`, 88000 AS `rent`, '【架空のサンプル物件】横浜駅やみなとみらい方面へ移動しやすい想定の部屋です。' AS `description`, 'images/property1.jpg' AS `image`
    UNION ALL SELECT '白楽グリーンコート', '白楽駅', '神奈川区', '神奈川大学（横浜キャンパス）', 62000, '【架空のサンプル物件】白楽駅周辺を想定した学生向けの部屋です。', 'images/property2.jpg'
    UNION ALL SELECT '常盤台テラス', '和田町駅', '保土ケ谷区', '横浜国立大学', 58000, '【架空のサンプル物件】常盤台方面へ通う学生を想定した部屋です。', 'images/property3.jpg'
    UNION ALL SELECT '金沢八景シーサイド', '金沢八景駅', '金沢区', '横浜市立大学（金沢八景キャンパス）', 57000, '【架空のサンプル物件】金沢八景駅周辺を想定したコンパクトな部屋です。', 'images/property4.jpg'
    UNION ALL SELECT '新横浜パークヒル', '新横浜駅', '港北区', 'yokohama', 72000, '【架空のサンプル物件】新幹線や市営地下鉄を利用しやすい想定の部屋です。', 'images/property1.jpg'
    UNION ALL SELECT '鶴見リバーコート', '鶴見駅', '鶴見区', 'kanagawa', 59000, '【架空のサンプル物件】鶴見駅周辺の生活を想定した部屋です。', 'images/property2.jpg'
    UNION ALL SELECT '関内シティハイツ', '関内駅', '中区', '令和日本語学院', 69000, '【架空のサンプル物件】関内駅周辺を想定した通学向けの部屋です。', 'images/property3.jpg'
    UNION ALL SELECT '川崎駅前ブリーズ', '川崎駅', '川崎区', 'kanagawa', 76000, '【架空のサンプル物件】川崎駅周辺の買い物や通勤に便利な想定の部屋です。', 'images/property4.jpg'
    UNION ALL SELECT '武蔵小杉セントラル', '武蔵小杉駅', '中原区', 'kanagawa', 82000, '【架空のサンプル物件】複数路線を使う暮らしを想定した部屋です。', 'images/property1.jpg'
    UNION ALL SELECT '溝の口ミュージックハイツ', '溝の口駅', '高津区', '洗足学園音楽大学', 67000, '【架空のサンプル物件】溝の口駅周辺を想定した学生向けの部屋です。', 'images/property2.jpg'
    UNION ALL SELECT '向ヶ丘遊園メゾン', '向ヶ丘遊園駅', '多摩区', '専修大学（生田キャンパス）', 59000, '【架空のサンプル物件】向ヶ丘遊園駅周辺を想定した学生向けの部屋です。', 'images/property3.jpg'
    UNION ALL SELECT '新百合ヶ丘グリーンコート', '新百合ヶ丘駅', '麻生区', 'kanagawa', 64000, '【架空のサンプル物件】新百合ヶ丘駅周辺を想定した静かな部屋です。', 'images/property4.jpg'
    UNION ALL SELECT '戸塚サンリーフ', '戸塚駅', '戸塚区', 'yokohama', 62000, '【架空のサンプル物件】戸塚駅周辺の生活を想定した部屋です。', 'images/property1.jpg'
    UNION ALL SELECT '鎌倉小町レジデンス', '鎌倉駅', '鎌倉市', 'kanagawa', 81000, '【架空のサンプル物件】鎌倉駅周辺を想定した落ち着いた部屋です。', 'images/property2.jpg'
    UNION ALL SELECT '藤沢サンライト', '藤沢駅', '藤沢市', 'kanagawa', 70000, '【架空のサンプル物件】藤沢駅周辺の暮らしを想定した部屋です。', 'images/property3.jpg'
    UNION ALL SELECT '横須賀ベイサイド', '横須賀中央駅', '横須賀市', 'kanagawa', 64000, '【架空のサンプル物件】横須賀中央駅周辺を想定した部屋です。', 'images/property4.jpg'
    UNION ALL SELECT '横浜西口スカイハイツ', '横浜駅', '西区', 'yokohama', 74000, '【架空のサンプル物件】横浜駅西口周辺を想定した明るいワンルームです。', 'images/demo-room-1.jpg'
    UNION ALL SELECT '東神奈川リーフコート', '横浜駅', '神奈川区', '神奈川大学（横浜キャンパス）', 63000, '【架空のサンプル物件】通学と買い物のしやすさを想定した部屋です。', 'images/demo-room-2.jpg'
    UNION ALL SELECT 'みなとみらいパークサイド', '桜木町駅', '西区', '神奈川大学（みなとみらいキャンパス）', 89000, '【架空のサンプル物件】みなとみらい周辺での暮らしを想定しています。', 'images/demo-room-3.jpg'
    UNION ALL SELECT '保土ケ谷ステーションハウス', '和田町駅', '保土ケ谷区', '横浜国立大学', 56000, '【架空のサンプル物件】大学へ通いやすい立地を想定した部屋です。', 'images/demo-room-4.jpg'
    UNION ALL SELECT '金沢文庫オーシャンビュー', '金沢八景駅', '金沢区', '横浜市立大学（金沢八景キャンパス）', 61000, '【架空のサンプル物件】海の近くの落ち着いた住環境を想定しています。', 'images/demo-room-5.jpg'
    UNION ALL SELECT '菊名フォレストレジデンス', '菊名駅', '港北区', 'yokohama', 68000, '【架空のサンプル物件】複数路線を使いやすい想定の部屋です。', 'images/demo-room-1.jpg'
    UNION ALL SELECT '日吉サニーハイム', '日吉駅', '港北区', '横浜国立大学', 66000, '【架空のサンプル物件】学生の一人暮らしを想定した部屋です。', 'images/demo-room-2.jpg'
    UNION ALL SELECT '新子安ブルーム', '鶴見駅', '鶴見区', '令和日本語学院', 59000, '【架空のサンプル物件】駅周辺の生活利便性を想定しています。', 'images/demo-room-3.jpg'
    UNION ALL SELECT '石川町クラシックハウス', '石川町駅', '中区', '神奈川大学（みなとみらいキャンパス）', 78000, '【架空のサンプル物件】横浜らしい街並みの近くを想定した部屋です。', 'images/demo-room-4.jpg'
    UNION ALL SELECT '戸塚リバーサイド', '戸塚駅', '戸塚区', 'yokohama', 64000, '【架空のサンプル物件】駅周辺で落ち着いて暮らせる想定の部屋です。', 'images/demo-room-5.jpg'
    UNION ALL SELECT '上大岡ガーデンテラス', '桜木町駅', '港南区', 'kanagawa', 62000, '【架空のサンプル物件】日常の買い物に便利な地域を想定しています。', 'images/demo-room-1.jpg'
    UNION ALL SELECT '青葉台グリーンヒル', '新横浜駅', '青葉区', 'kanagawa', 71000, '【架空のサンプル物件】緑の多い住宅地を想定した部屋です。', 'images/demo-room-2.jpg'
    UNION ALL SELECT '川崎リバーゲート', '川崎駅', '川崎区', '令和日本語学院', 73000, '【架空のサンプル物件】川崎駅周辺での通勤通学を想定しています。', 'images/demo-room-3.jpg'
    UNION ALL SELECT '鹿島田コンフォート', '川崎駅', '幸区', 'kanagawa', 67000, '【架空のサンプル物件】静かな住宅街の暮らしを想定した部屋です。', 'images/demo-room-4.jpg'
    UNION ALL SELECT '武蔵小杉タワーサイド', '武蔵小杉駅', '中原区', 'kanagawa', 94000, '【架空のサンプル物件】駅近の便利な暮らしを想定した部屋です。', 'images/demo-room-5.jpg'
    UNION ALL SELECT '高津リラックスハイツ', '溝の口駅', '高津区', '洗足学園音楽大学', 65000, '【架空のサンプル物件】音楽大学への通学を想定した部屋です。', 'images/demo-room-1.jpg'
    UNION ALL SELECT '登戸リバーコート', '登戸駅', '多摩区', '専修大学（生田キャンパス）', 60000, '【架空のサンプル物件】登戸駅周辺の学生生活を想定しています。', 'images/demo-room-2.jpg'
    UNION ALL SELECT '宮前平ナチュラルハウス', '溝の口駅', '宮前区', 'kanagawa', 69000, '【架空のサンプル物件】緑のある住宅地を想定した部屋です。', 'images/demo-room-3.jpg'
    UNION ALL SELECT '新百合ヶ丘ステラ', '新百合ヶ丘駅', '麻生区', '令和日本語学院', 70000, '【架空のサンプル物件】駅周辺で便利に暮らす想定の部屋です。', 'images/demo-room-4.jpg'
    UNION ALL SELECT '鎌倉由比ガ浜ハウス', '鎌倉駅', '鎌倉市', 'kanagawa', 85000, '【架空のサンプル物件】海と街の近くで暮らす想定の部屋です。', 'images/demo-room-5.jpg'
    UNION ALL SELECT '藤沢サーフサイド', '藤沢駅', '藤沢市', 'yokohama', 72000, '【架空のサンプル物件】海辺の街での一人暮らしを想定しています。', 'images/demo-room-1.jpg'
    UNION ALL SELECT '逗子なぎさコート', '逗子駅', '逗子市', '横浜市立大学（金沢八景キャンパス）', 76000, '【架空のサンプル物件】海の近くのゆったりした部屋を想定しています。', 'images/demo-room-2.jpg'
    UNION ALL SELECT '大和セントラルハイツ', '大和駅', '大和市', '横浜国立大学', 61000, '【架空のサンプル物件】駅周辺の暮らしやすさを想定しています。', 'images/demo-room-3.jpg'
    UNION ALL SELECT '町田サクラレジデンス', '町田駅', '町田市', '専修大学（生田キャンパス）', 68000, '【架空のサンプル物件】町田駅周辺を想定した学生向けの部屋です。', 'images/demo-room-4.jpg'
    UNION ALL SELECT '蒲田イーストハイム', '蒲田駅', '大田区', '令和日本語学院', 77000, '【架空のサンプル物件】都内への移動に便利な想定の部屋です。', 'images/demo-room-5.jpg'
    UNION ALL SELECT '品川ベイフロント', '品川駅', '品川区', '神奈川大学（みなとみらいキャンパス）', 98000, '【架空のサンプル物件】品川駅周辺の通勤を想定した部屋です。', 'images/demo-room-1.jpg'
    UNION ALL SELECT '横須賀中央ブルーコースト', '横須賀中央駅', '横須賀市', 'yokohama', 58000, '【架空のサンプル物件】海の見える街の暮らしを想定しています。', 'images/demo-room-2.jpg'
    UNION ALL SELECT 'センター北パークヒル', '新横浜駅', '都筑区', '洗足学園音楽大学', 75000, '【架空のサンプル物件】公園の多い地域を想定したファミリー向けの部屋です。', 'images/demo-room-3.jpg'
) AS samples
WHERE NOT EXISTS (
    SELECT 1 FROM `properties` AS existing WHERE existing.`name` = samples.`name`
);

ALTER TABLE `properties` AUTO_INCREMENT = 49;

UPDATE `properties` SET `layout` = '1R' WHERE `id` IN (2, 8, 15, 21, 25, 31, 38);
UPDATE `properties` SET `layout` = '1DK' WHERE `id` IN (6, 10, 14, 23, 29, 35, 42);
UPDATE `properties` SET `layout` = '1LDK' WHERE `id` IN (5, 9, 13, 18, 22, 27, 32, 37, 43, 48);
UPDATE `properties` SET `layout` = '2DK' WHERE `id` IN (12, 19, 24, 30, 34, 40, 45);
UPDATE `properties` SET `layout` = '2LDK' WHERE `id` IN (20, 28, 36, 41, 46);
UPDATE `properties` SET `layout` = '3LDK' WHERE `id` IN (17, 33, 39, 44, 47);
UPDATE `properties` SET `pet_allowed` = 1 WHERE `id` IN (5, 8, 11, 14, 18, 21, 24, 27, 30, 33, 36, 39, 42, 45, 48);

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `customers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `password_version` int(10) unsigned NOT NULL DEFAULT 0,
  `email_verified_at` datetime DEFAULT NULL,
  `terms_accepted_at` datetime DEFAULT NULL,
  `privacy_accepted_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `customer_favorites` (
  `customer_id` int(11) NOT NULL,
  `property_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`customer_id`, `property_id`),
  KEY `property_id` (`property_id`),
  CONSTRAINT `customer_favorites_customer_fk` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `customer_favorites_property_fk` FOREIGN KEY (`property_id`) REFERENCES `properties` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `customer_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `purpose` varchar(20) NOT NULL,
  `token_hash` char(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `token_hash` (`token_hash`),
  KEY `customer_purpose` (`customer_id`, `purpose`),
  CONSTRAINT `customer_tokens_customer_fk` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `auth_login_attempts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `rate_key` char(64) NOT NULL,
  `attempted_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `rate_key_attempted_at` (`rate_key`, `attempted_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `auth_security_events` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `event_type` varchar(40) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `customer_event_created` (`customer_id`, `event_type`, `created_at`),
  KEY `created_at` (`created_at`),
  CONSTRAINT `auth_security_events_customer_fk` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;
