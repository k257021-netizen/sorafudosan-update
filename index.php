<?php
require 'db.php';
$homeFavoriteIds = !empty($_SESSION['customer_id'])
    ? array_fill_keys(customer_favorite_ids($pdo, (int)$_SESSION['customer_id']), true)
    : [];
$homeFavoriteCsrf = !empty($_SESSION['customer_id']) ? csrf_token() : '';
?>
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#f5f7fb">
    <meta name="description" content="横浜・川崎エリアのお部屋探しを、Sora Livingがお手伝いします。学校や駅、家賃の条件から物件を検索できます。">
    <title>お部屋探し | Sora Living</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="home-page">
    <a class="skip-link" href="#main-content">本文へ移動</a>
    <div class="page-shell">
        <header class="site-header">
            <a class="brand" href="index.php" aria-label="Sora Living ホーム">
                <img src="images/Gemini_Generated_Image_ku7bpvku7bpvku7b.jpg" alt="Sora Living">
            </a>
            <nav class="header-actions" aria-label="アカウントとお問い合わせ">
                <?php if (!empty($_SESSION['customer_id'])): ?>
                    <a class="header-account" href="account.php">マイページ</a>
                    <form class="header-logout-form" method="POST" action="member-logout.php">
                        <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                        <button class="header-account" type="submit">ログアウト</button>
                    </form>
                <?php elseif (!empty($_SESSION['user_id'])): ?>
                    <a class="header-account" href="admin/index.php">管理画面</a>
                    <form class="header-logout-form" method="POST" action="logout.php">
                        <input type="hidden" name="csrf" value="<?= h(csrf_token()) ?>">
                        <button class="header-account" type="submit">ログアウト</button>
                    </form>
                <?php else: ?>
                    <a class="header-account" href="member-login.php">ログイン</a>
                    <a class="header-account" href="register.php">新規登録</a>
                <?php endif; ?>
                <a class="header-contact" href="contact.php">お問い合わせ</a>
            </nav>
        </header>

        <main id="main-content">
            <section class="hero" aria-labelledby="hero-title">
                <p class="eyebrow">SORA LIVING ・ お部屋探し</p>
                <h1 id="hero-title">新しい暮らしを、<br>ここから始めよう。</h1>
                <p class="hero-copy">学校や駅、住みたい地域から、あなたにぴったりのお部屋を探せます。</p>

                <form class="search-container" action="search.php" method="GET" role="search">
                    <label class="visually-hidden" for="keyword">学校・駅・地域・物件名で検索</label>
                    <input id="keyword" type="search" placeholder="学校・駅・地域・物件名で検索" name="q" class="search-input" maxlength="100" required>
                    <button type="submit" class="search-button">
                        <svg xmlns="http://www.w3.org/2000/svg" width="19" height="19" fill="currentColor" viewBox="0 0 16 16" aria-hidden="true">
                            <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z" />
                        </svg>
                        <span>検索</span>
                    </button>
                </form>
            </section>

            <section class="browse-panel" aria-labelledby="browse-title">
                <div class="section-heading">
                    <p class="eyebrow">SEARCH BY</p>
                    <h2 id="browse-title">探し方を選ぶ</h2>
                </div>
                <div class="container">
                    <button class="option" id="searchfromschool" type="button" aria-controls="school-section" aria-expanded="false">
                        <img src="images/school.png" alt="" aria-hidden="true">
                        <span>学校・エリアから探す</span>
                    </button>
                    <button class="option" id="searchfromstation" type="button" aria-controls="station-section" aria-expanded="false">
                        <img src="images/train.png" alt="" aria-hidden="true">
                        <span>駅から探す</span>
                    </button>
                    <button class="option" id="searchfromarea" type="button" aria-controls="area-section" aria-expanded="false">
                        <img src="images/google-maps.png" alt="" aria-hidden="true">
                        <span>地域から探す</span>
                    </button>
                    <a class="option option-link" href="favorites.php">
                        <img src="images/heart.png" alt="" aria-hidden="true">
                        <span>お気に入り</span>
                    </a>
                    <button class="option" id="searchbycondition" type="button" aria-controls="condition-section" aria-expanded="false">
                        <img src="images/search.png" alt="" aria-hidden="true">
                        <span>条件から探す</span>
                    </button>
                </div>
            </section>

            <section id="school-section" class="search-section search-panel" aria-labelledby="school-title" hidden>
                <h2 id="school-title">学校・エリアから探す</h2>
                <form action="search.php" method="GET" class="filter-form">
                    <label for="school">通学先・エリア</label>
                    <select id="school" name="school" required>
                        <option value="">選択してください</option>
                        <optgroup label="横浜市">
                            <option value="横浜国立大学">横浜国立大学</option>
                            <option value="神奈川大学（横浜キャンパス）">神奈川大学（横浜キャンパス）</option>
                            <option value="神奈川大学（みなとみらいキャンパス）">神奈川大学（みなとみらいキャンパス）</option>
                            <option value="横浜市立大学（金沢八景キャンパス）">横浜市立大学（金沢八景キャンパス）</option>
                        </optgroup>
                        <optgroup label="川崎市">
                            <option value="専修大学（生田キャンパス）">専修大学（生田キャンパス）</option>
                            <option value="洗足学園音楽大学">洗足学園音楽大学</option>
                        </optgroup>
                        <option value="令和日本語学院">令和日本語学院</option>
                        <option value="yokohama">横浜エリア（既存登録）</option>
                        <option value="kanagawa">神奈川県内（既存登録）</option>
                    </select>
                    <button type="submit" class="primary-button">物件を探す</button>
                </form>
            </section>

            <section id="station-section" class="search-section search-panel" aria-labelledby="station-title" hidden>
                <h2 id="station-title">駅から探す</h2>
                <form action="search.php" method="GET" class="filter-form">
                    <label for="station">最寄り駅</label>
                    <select id="station" name="station" required>
                        <option value="">選択してください</option>
                        <optgroup label="横浜エリア">
                            <option value="横浜駅">横浜駅</option>
                            <option value="新横浜駅">新横浜駅</option>
                            <option value="菊名駅">菊名駅</option>
                            <option value="日吉駅">日吉駅</option>
                            <option value="鶴見駅">鶴見駅</option>
                            <option value="桜木町駅">桜木町駅</option>
                            <option value="関内駅">関内駅</option>
                            <option value="石川町駅">石川町駅</option>
                            <option value="戸塚駅">戸塚駅</option>
                            <option value="金沢八景駅">金沢八景駅</option>
                            <option value="白楽駅">白楽駅</option>
                            <option value="和田町駅">和田町駅</option>
                        </optgroup>
                        <optgroup label="川崎エリア">
                            <option value="川崎駅">川崎駅</option>
                            <option value="京急川崎駅">京急川崎駅</option>
                            <option value="武蔵小杉駅">武蔵小杉駅</option>
                            <option value="溝の口駅">溝の口駅</option>
                            <option value="武蔵溝ノ口駅">武蔵溝ノ口駅</option>
                            <option value="登戸駅">登戸駅</option>
                            <option value="向ヶ丘遊園駅">向ヶ丘遊園駅</option>
                            <option value="新百合ヶ丘駅">新百合ヶ丘駅</option>
                        </optgroup>
                        <optgroup label="近隣エリア">
                            <option value="鎌倉駅">鎌倉駅</option>
                            <option value="藤沢駅">藤沢駅</option>
                            <option value="逗子駅">逗子駅</option>
                            <option value="横須賀中央駅">横須賀中央駅</option>
                            <option value="大和駅">大和駅</option>
                            <option value="町田駅">町田駅</option>
                            <option value="蒲田駅">蒲田駅</option>
                            <option value="品川駅">品川駅</option>
                        </optgroup>
                    </select>
                    <button type="submit" class="primary-button">物件を探す</button>
                </form>
            </section>

            <section id="area-section" class="search-section search-panel" aria-labelledby="area-title" hidden>
                <h2 id="area-title">地域から探す</h2>
                <form action="search.php" method="GET" class="filter-form">
                    <label for="area">住みたい地域</label>
                    <select id="area" name="area" required>
                        <option value="">選択してください</option>
                        <optgroup label="横浜市">
                            <option value="鶴見区">鶴見区</option><option value="神奈川区">神奈川区</option>
                            <option value="西区">西区</option><option value="中区">中区</option>
                            <option value="南区">南区</option><option value="港南区">港南区</option>
                            <option value="保土ケ谷区">保土ケ谷区</option><option value="旭区">旭区</option>
                            <option value="磯子区">磯子区</option><option value="金沢区">金沢区</option>
                            <option value="港北区">港北区</option><option value="緑区">緑区</option>
                            <option value="青葉区">青葉区</option><option value="都筑区">都筑区</option>
                            <option value="戸塚区">戸塚区</option><option value="栄区">栄区</option>
                            <option value="泉区">泉区</option><option value="瀬谷区">瀬谷区</option>
                        </optgroup>
                        <optgroup label="川崎市">
                            <option value="川崎区">川崎区</option><option value="幸区">幸区</option>
                            <option value="中原区">中原区</option><option value="高津区">高津区</option>
                            <option value="宮前区">宮前区</option><option value="多摩区">多摩区</option>
                            <option value="麻生区">麻生区</option>
                        </optgroup>
                        <optgroup label="近隣エリア">
                            <option value="鎌倉市">鎌倉市</option><option value="藤沢市">藤沢市</option>
                            <option value="逗子市">逗子市</option><option value="横須賀市">横須賀市</option>
                            <option value="大和市">大和市</option><option value="町田市">町田市</option>
                            <option value="大田区">東京都大田区</option><option value="品川区">東京都品川区</option>
                        </optgroup>
                        <optgroup label="既存登録（市全体）">
                            <option value="横浜市">横浜市（市全体）</option>
                            <option value="川崎市">川崎市（市全体）</option>
                        </optgroup>
                    </select>
                    <button type="submit" class="primary-button">物件を探す</button>
                </form>
            </section>

            <section id="condition-section" class="search-section search-panel" aria-labelledby="condition-title" hidden>
                <h2 id="condition-title">条件から探す</h2>
                <form action="search.php" method="GET" class="filter-form filter-form-inline">
                    <label for="max-rent">家賃の上限</label>
                    <select id="max-rent" name="max_rent">
                        <option value="">指定なし</option>
                        <option value="50000">50,000円まで</option>
                        <option value="60000">60,000円まで</option>
                        <option value="70000">70,000円まで</option>
                        <option value="80000">80,000円まで</option>
                    </select>
                    <label for="condition-station">最寄り駅</label>
                    <select id="condition-station" name="station">
                        <option value="">指定なし</option>
                        <option value="横浜駅">横浜駅</option><option value="新横浜駅">新横浜駅</option>
                        <option value="川崎駅">川崎駅</option><option value="武蔵小杉駅">武蔵小杉駅</option>
                        <option value="溝の口駅">溝の口駅</option><option value="桜木町駅">桜木町駅</option>
                        <option value="関内駅">関内駅</option><option value="金沢八景駅">金沢八景駅</option>
                        <option value="戸塚駅">戸塚駅</option><option value="鎌倉駅">鎌倉駅</option>
                        <option value="藤沢駅">藤沢駅</option><option value="逗子駅">逗子駅</option>
                        <option value="大和駅">大和駅</option><option value="町田駅">町田駅</option>
                    </select>
                    <label for="condition-layout">間取り</label>
                    <select id="condition-layout" name="layout">
                        <option value="">指定なし</option>
                        <option value="1R">1R</option><option value="1K">1K</option>
                        <option value="1DK">1DK</option><option value="1LDK">1LDK</option>
                        <option value="2DK">2DK</option><option value="2LDK">2LDK</option><option value="3LDK">3LDK</option>
                    </select>
                    <label for="condition-pets">ペット</label>
                    <select id="condition-pets" name="pet_allowed">
                        <option value="">指定なし</option>
                        <option value="1">ペット可</option>
                        <option value="0">ペット不可</option>
                    </select>
                    <label for="condition-area">専有面積の下限（m²）</label>
                    <select id="condition-area" name="min_area"><option value="">指定なし</option><option value="20">20m²以上</option><option value="30">30m²以上</option><option value="40">40m²以上</option></select>
                    <label for="condition-initial-cost">初期費用の上限</label>
                    <select id="condition-initial-cost" name="max_initial_cost"><option value="">指定なし</option><option value="150000">150,000円まで</option><option value="250000">250,000円まで</option><option value="350000">350,000円まで</option></select>
                    <button type="submit" class="primary-button">物件を探す</button>
                </form>
            </section>

            <section class="search-section featured-section" aria-labelledby="featured-title">
                <div class="section-heading">
                    <p class="eyebrow">SAMPLE ROOMS</p>
                    <h2 id="featured-title">おすすめの部屋</h2>
                    <p class="hero-copy">掲載内容はイメージ用のサンプルです。気になる部屋は詳細をご覧ください。</p>
                </div>
                <div id="property-list">
                    <article class="property-card">
                        <a href="property.php?id=1">
                            <img src="images/property1.jpg" alt="サンプルマンションのイメージ写真" loading="lazy">
                            <h3>サンプルマンション</h3>
                        </a>
                        <p class="sample-badge">架空のサンプル</p>
                        <p>最寄り駅：横浜駅</p><p>横浜市</p><p>間取り：1K ・ ペット不可</p>
                        <p class="price">家賃 60,000円</p>
                        <button type="button" class="fav-button" data-id="1" data-authenticated="<?= !empty($_SESSION['customer_id']) ? '1' : '0' ?>" data-favorite="<?= isset($homeFavoriteIds[1]) ? '1' : '0' ?>" data-csrf="<?= h($homeFavoriteCsrf) ?>"></button>
                    </article>
                    <article class="property-card">
                        <a href="property.php?id=2">
                            <img src="images/property2.jpg" alt="さくらハイツのイメージ写真" loading="lazy">
                            <h3>さくらハイツ</h3>
                        </a>
                        <p class="sample-badge">架空のサンプル</p>
                        <p>最寄り駅：桜木町駅</p><p>横浜市</p><p>間取り：1R ・ ペット不可</p>
                        <p class="price">家賃 55,000円</p>
                        <button type="button" class="fav-button" data-id="2" data-authenticated="<?= !empty($_SESSION['customer_id']) ? '1' : '0' ?>" data-favorite="<?= isset($homeFavoriteIds[2]) ? '1' : '0' ?>" data-csrf="<?= h($homeFavoriteCsrf) ?>"></button>
                    </article>
                    <article class="property-card">
                        <a href="property.php?id=3">
                            <img src="images/property3.jpg" alt="川崎レジデンスのイメージ写真" loading="lazy">
                            <h3>川崎レジデンス</h3>
                        </a>
                        <p class="sample-badge">架空のサンプル</p>
                        <p>最寄り駅：川崎駅</p><p>川崎市</p><p>間取り：1K ・ ペット不可</p>
                        <p class="price">家賃 65,000円</p>
                        <button type="button" class="fav-button" data-id="3" data-authenticated="<?= !empty($_SESSION['customer_id']) ? '1' : '0' ?>" data-favorite="<?= isset($homeFavoriteIds[3]) ? '1' : '0' ?>" data-csrf="<?= h($homeFavoriteCsrf) ?>"></button>
                    </article>
                    <article class="property-card">
                        <a href="property.php?id=4">
                            <img src="images/property4.jpg" alt="レオパレス24-12のイメージ写真" loading="lazy">
                            <h3>レオパレス24-12</h3>
                        </a>
                        <p class="sample-badge">架空のサンプル</p>
                        <p>最寄り駅：川崎駅</p><p>川崎市</p><p>間取り：1K ・ ペット不可</p>
                        <p class="price">家賃 80,000円</p>
                        <button type="button" class="fav-button" data-id="4" data-authenticated="<?= !empty($_SESSION['customer_id']) ? '1' : '0' ?>" data-favorite="<?= isset($homeFavoriteIds[4]) ? '1' : '0' ?>" data-csrf="<?= h($homeFavoriteCsrf) ?>"></button>
                    </article>
                </div>
                <p class="featured-more"><a class="primary-button" href="search.php?browse=1">すべての物件を見る</a></p>
            </section>
        </main>

        <footer class="site-footer">
            <p><a href="contact.php">お問い合わせ</a><span aria-hidden="true">・</span><a href="privacy.php">プライバシーポリシー</a><span aria-hidden="true">・</span><a href="terms.php">利用規約</a><span aria-hidden="true">・</span><a href="login.php">管理者ログイン</a></p>
            <small>&copy; Sora Living</small>
        </footer>
    </div>
    <script src="script.js"></script>
    <script src="favorites.js"></script>
</body>
</html>
