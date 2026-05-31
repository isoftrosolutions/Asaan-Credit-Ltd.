-- Phase 2: Blog mini-CMS
-- Apply on production after pulling:  mysql -u <user> -p <db> < database/migration-blog.sql

CREATE TABLE IF NOT EXISTS `blog_posts` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `slug` varchar(255) NOT NULL,
  `excerpt` varchar(500) DEFAULT NULL,
  `body` mediumtext NOT NULL,
  `author` varchar(120) NOT NULL DEFAULT 'Asaan Capital',
  `status` varchar(20) NOT NULL DEFAULT 'draft',
  `published_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `blog_posts_slug_unique` (`slug`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `blog_posts` (`title`, `slug`, `excerpt`, `body`, `author`, `status`, `published_at`, `created_at`, `updated_at`) VALUES
('How to Value a Small Business in Nepal', 'how-to-value-a-small-business-in-nepal', 'A practical walkthrough of the three methods investors use to value SMEs — and how to apply them to a Nepali business.', 'Valuing a business is part science, part judgement. Most buyers and investors lean on three approaches, and the truth usually sits somewhere between them.\n\nThe first is trading comparables: looking at how publicly listed companies in the same sector are priced relative to their earnings (EV/EBITDA). The second is transaction comparables, which uses the prices paid in actual deals for similar private businesses. The third is discounted cash flow, which projects future cash and discounts it back to today.\n\nFor a Nepali SME, comparable multiples are usually the most reliable starting point. Apply a sector multiple to your EBITDA, then adjust for growth and how long you have been operating. Our free calculator does exactly this — try it before you talk to any buyer.', 'Asaan Capital', 'published', '2026-05-10 09:00:00', '2026-05-10 09:00:00', '2026-05-10 09:00:00'),
('5 Things Investors Look For Before They Fund You', '5-things-investors-look-for-before-they-fund-you', 'Capital follows conviction. Here is what convinces a Nepali investor to move from interest to a cheque.', 'Raising money is less about a perfect pitch and more about reducing the investor''s perceived risk. Five things move the needle more than anything else.\n\nClean financials. If your numbers are organised and believable, you are already ahead of most. Traction. Revenue, repeat customers, or signed contracts speak louder than projections. A clear use of funds. Investors want to know exactly what their money buys and what milestone it unlocks.\n\nA capable team is the fourth — people back people. And finally, a realistic valuation. Over-pricing your round is the fastest way to stall a deal. Get these five right and the conversation changes completely.', 'Asaan Capital', 'published', '2026-05-20 09:00:00', '2026-05-20 09:00:00', '2026-05-20 09:00:00'),
('Selling Your Business Confidentially: A Short Guide', 'selling-your-business-confidentially-a-short-guide', 'How to find a buyer without tipping off staff, suppliers, and competitors.', 'The biggest fear most owners have when selling is exposure. If word gets out too early, staff get nervous, competitors pounce, and suppliers renegotiate.\n\nThe answer is a staged disclosure. Start with an anonymous profile that shares the shape of the opportunity — sector, size, and financial highlights — without naming the business. Only when a genuine, verified buyer expresses interest do you reveal your identity, and even then on your terms.\n\nThis is exactly how matching works on our platform: contact details stay private until there is mutual interest. You stay in control of who learns what, and when.', 'Asaan Capital', 'published', '2026-05-28 09:00:00', '2026-05-28 09:00:00', '2026-05-28 09:00:00');
