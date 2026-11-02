-- Safe to import repeatedly. Existing edited document content is never replaced.
USE `sora`;
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `site_documents` (
  `document_key` varchar(32) NOT NULL,
  `content` mediumtext NOT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`document_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT IGNORE INTO `site_documents` (`document_key`, `content`) VALUES
('terms', '本規約は、Sora Livingの物件検索、会員機能、お問い合わせ機能の利用条件を定めます。\n\n## 利用時のお願い\n利用者は正確な情報を登録し、第三者へのなりすまし、不正アクセス、サービス運営を妨げる行為を行わないものとします。\n\n## 掲載情報について\n掲載情報や写真は参考情報です。募集状況、費用、設備等は変わる場合があるため、契約前にお問い合わせのうえご確認ください。サンプル物件は架空の情報です。\n\n## サービスの変更\n保守や運営上の必要に応じ、機能や掲載内容を変更・停止することがあります。\n\n制定日：2026年10月21日'),
('privacy', 'Sora Living（以下「当サイト」）は、アカウント登録、お気に入り保存、お問い合わせ対応のために、氏名、メールアドレス、電話番号、お問い合わせ内容を取得します。\n\n## 利用目的\n物件探し機能の提供、本人確認、問い合わせへの回答、サービス改善および不正利用防止に利用します。アカウントのセキュリティを守るため、パスワード変更の日時、接続元IPアドレス、ブラウザー情報を記録し、セキュリティ記録は作成から180日後に削除します。パスワードを変更または再設定した場合、登録メールアドレスに通知します。\n\n## 第三者提供・保管\n法令に基づく場合を除き、本人の同意なく個人情報を第三者へ提供しません。利用目的に必要な期間、安全管理に配慮して保管し、不要になった情報は適切に削除します。\n\n## お問い合わせ・開示請求\n個人情報に関する相談や開示・訂正・削除の希望は、サイトのお問い合わせフォームからご連絡ください。\n\n制定日：2026年11月2日');
