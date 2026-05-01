<?php
/**
 * Massive Spam Keyword Database v2.0
 * 
 * Divided into categories for easier management.
 * Includes German and English terms.
 */

return [
	// Finance, Crypto & Trading
	'bitcoin', 'crypto', 'krypto', 'trading', 'profit', 'investment', 'loan', 'kredit', 'geld', 'money', 'rich', 'wealth', 
	'ethereum', 'blockchain', 'wallet', 'forex', 'stocks', 'dividende', 'verdienen', 'passiv', 'income', 'einkommen',
	'metatrader', 'meta-trader', 'signals', 'expert advisor', 'mining', 'staking', 'passive', 'return', 'daily profit',
	'binary options', 'invest', 'cash', 'loan offer', 'kreditangebot', 'finanzen', 'darlehen',
	
	// Pharma, Health & Beauty (DE/EN)
	'viagra', 'cialis', 'levitra', 'pills', 'pillen', 'pharmacy', 'apotheke', 'weight loss', 'diät', 'diet', 'testosterone',
	'medication', 'nahrungsergänzung', 'supplements', 'muscle', 'gym', 'steroids', 'hgh', 'anabolika', 'abnehmen',
	'natural', 'herbal', 'potenzmittel', 'erection', 'cialis-generic', 'viagra-generic',
	
	// Erotics, Dating & Romance Scams
	'sex', 'porn', 'adult', 'dating', 'girl', 'boy', 'webcam', 'escort', 'hot', 'naked', 'nackt', 'erotik', 'cam', 'onlyfans',
	'flirt', 'blind date', 'hookup', 'xxx', 'horny', 'singles', 'dating-service', 'widow', 'lonely', 'looking for',
	'sugar daddy', 'sugar baby', 'relationship', 'meet girls', 'mature', 'milf', 'single mam', 'single lady',
	
	// Marketing, SMM & Bots
	'promo', 'discount', 'rabatt', 'cheap', 'günstig', 'billig', 'free', 'kostenlos', 'gratis', 'offer', 'angebot', 'win', 
	'gewinn', 'gift', 'present', 'geschenk', 'amazon', 'netflix', 'spotify', 'card', 'voucher', 'gutschein', 'claim',
	'seo', 'traffic', 'backlink', 'marketing', 'ads', 'werbung', 'followers', 'abonnenten', 'follow back', 'optimization',
	'smm', 'panel', 'reseller', 'engagement', 'likes', 'boost', 'growth', 'api', 'social media', 'tiktok', 'telegram',
	
	// Phishing, Security & Scams
	// NOTE: We deliberately keep this list narrow. Words like "account", "verify",
	// "login", "support", "service", "official", "warning", "attention" are
	// extremely common in legitimate communication (newsletters, support replies,
	// onboarding mails). Including them here used to produce massive false
	// positives — most newsletters mention "your account" at some point.
	// What we do match: specific, scam-typical phrases that practically never
	// appear outside phishing/crypto-fraud contexts.
	'suspended', 'compliance', 'unusual activity',
	'resolution-center', 'secret phrase', 'mnemonic', 'seed phrase',
	'limited', 'begrenzt', 'exclusive', 'exklusiv', 'invoice', 'overdue', 'überfällig',
	'payment',
	
	// General Bot Phrases & Trash
	'best price', 'work from home', 'job offer', 'apply now', 'click here',
	'visit my website', 'rechnung', 'check this', 'must see', 'sonderangebot', 'jetzt zugreifen',
	'vorteilspreis', 'nur heute'
];
