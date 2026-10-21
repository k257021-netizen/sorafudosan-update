-- Import this file after sora.sql to add the fictional demo listings to an existing database.
-- It is safe to import repeatedly: rows with the same property name are skipped.
USE `sora`;

UPDATE `properties`
SET `description` = CONCAT('【架空のサンプル物件】', COALESCE(`description`, ''))
WHERE `name` IN ('サンプルマンション', 'さくらハイツ', '川崎レジデンス', 'レオパレス24-12')
  AND `description` NOT LIKE '【架空のサンプル物件】%';

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

UPDATE `properties` SET `layout` = '1R' WHERE `name` IN ('さくらハイツ', '金沢八景シーサイド', '向ヶ丘遊園メゾン', '横浜西口スカイハイツ', '金沢文庫オーシャンビュー', '上大岡ガーデンテラス', '宮前平ナチュラルハウス');
UPDATE `properties` SET `layout` = '1DK' WHERE `name` IN ('白楽グリーンコート', '鶴見リバーコート', '溝の口ミュージックハイツ', 'みなとみらいパークサイド', '石川町クラシックハウス', '武蔵小杉タワーサイド', '逗子なぎさコート');
UPDATE `properties` SET `layout` = '1LDK' WHERE `name` IN ('横浜みなとレジデンス', '新横浜パークヒル', '武蔵小杉セントラル', '鎌倉小町レジデンス', '東神奈川リーフコート', '日吉サニーハイム', '青葉台グリーンヒル', '登戸リバーコート', '大和セントラルハイツ', 'センター北パークヒル');
UPDATE `properties` SET `layout` = '2DK' WHERE `name` IN ('川崎駅前ブリーズ', '藤沢サンライト', '保土ケ谷ステーションハウス', '戸塚リバーサイド', '鹿島田コンフォート', '鎌倉由比ガ浜ハウス', '蒲田イーストハイム');
UPDATE `properties` SET `layout` = '2LDK' WHERE `name` IN ('横須賀ベイサイド', '新子安ブルーム', '高津リラックスハイツ', '藤沢サーフサイド', '品川ベイフロント');
UPDATE `properties` SET `layout` = '3LDK' WHERE `name` IN ('戸塚サンリーフ', '川崎リバーゲート', '新百合ヶ丘ステラ', '町田サクラレジデンス', '横須賀中央ブルーコースト');
UPDATE `properties` SET `pet_allowed` = 1 WHERE `name` IN ('横浜みなとレジデンス', '金沢八景シーサイド', '関内シティハイツ', '溝の口ミュージックハイツ', '鎌倉小町レジデンス', '横浜西口スカイハイツ', '保土ケ谷ステーションハウス', '日吉サニーハイム', '戸塚リバーサイド', '川崎リバーゲート', '高津リラックスハイツ', '新百合ヶ丘ステラ', '逗子なぎさコート', '蒲田イーストハイム', 'センター北パークヒル');
