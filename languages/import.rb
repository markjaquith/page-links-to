#!/usr/bin/ruby
langs = {
	'af' => 'af',
	'ak' => 'ak',
	'ar' => 'ar',
	'as' => 'as',
	'az' => 'az',
	'azb' => 'azb',
	'az_TR' => 'az-tr',
	'ba' => 'ba',
	'bal' => 'bal',
	'bg_BG' => 'bg',
	'bn_BD' => 'bn',
	'bs_BA' => 'bs',
	'ca' => 'ca',
	'ckb' => 'ckb',
	'co' => 'co',
	'cs_CZ' => 'cs',
	'cy' => 'cy',
	'da_DK' => 'da',
	'de_DE' => 'de',
	'dv' => 'dv',
	'el' => 'el',
	'en_US' => 'en',
	'en_CA' => 'en-ca',
	'en_GB' => 'en-gb',
	'eo' => 'eo',
	'es_CL' => 'es-cl',
	'es_MX' => 'es-mx',
	'es_PE' => 'es-pe',
	'es_PR' => 'es-pr',
	'es_VE' => 'es-ve',
	'es_CO' => 'es-co',
	'es_ES' => 'es',
	'et' => 'et',
	'eu' => 'eu',
	'fa_IR' => 'fa',
	'fa_AF' => 'fa-af',
	'fi' => 'fi',
	'fo' => 'fo',
	'fr_FR' => 'fr',
	'fr_BE' => 'fr-be',
	'fy' => 'fy',
	'gd' => 'gd',
	'gl_ES' => 'gl',
	'gn' => 'gn',
	'gsw' => 'gsw',
	'gu_IN' => 'gu',
	'haw_US' => 'haw',
	'haz' => 'haz',
	'he_IL' => 'he',
	'hi_IN' => 'hi',
	'hr' => 'hr',
	'hu_HU' => 'hu',
	'hy' => 'hy',
	'id_ID' => 'id',
	'is_IS' => 'is',
	'it_IT' => 'it',
	'ja' => 'ja',
	'jv_ID' => 'jv',
	'ka_GE' => 'ka',
	'kk' => 'kk',
	'kn' => 'kn',
	'ko_KR' => 'ko',
	'ky_KY' => 'ky',
	'lb_LU' => 'lb',
	'li' => 'li',
	'lo' => 'lo',
	'lt_LT' => 'lt',
	'lv' => 'lv',
	'me_ME' => 'me',
	'mg_MG' => 'mg',
	'mk_MK' => 'mk',
	'ml_IN' => 'ml',
	'ms_MY' => 'ms',
	'my_MM' => 'mya',
	'ne_NP' => 'ne',
	'nb_NO' => 'nb',
	'nl_NL' => 'nl',
	'nl_BE' => 'nl-be',
	'nn_NO' => 'nn',
	'os' => 'os',
	'pa_IN' => 'pa',
	'pl_PL' => 'pl',
	'pt_BR' => 'pt-br',
	'pt_PT' => 'pt',
	'ps' => 'ps',
	'ro_RO' => 'ro',
	'ru_RU' => 'ru',
	'ru_UA' => 'ru-ua',
	'rue' => 'rue',
	'rup_MK' => 'rup',
	'sah' => 'sah',
	'sa_IN' => 'sa-in',
	'sd_PK' => 'sd',
	'si_LK' => 'si',
	'sk_SK' => 'sk',
	'sl_SI' => 'sl',
	'so_SO' => 'so',
	'sq' => 'sq',
	'sr_RS' => 'sr',
	'srd' => 'srd',
	'su_ID' => 'su',
	'sv_SE' => 'sv',
	'sw' => 'sw',
	'ta_IN' => 'ta',
	'ta_LK' => 'ta-lk',
	'te' => 'te',
	'tg' => 'tg',
	'th' => 'th',
	'tl' => 'tl',
	'tr_TR' => 'tr',
	'tt_RU' => 'tt',
	'tuk' => 'tuk',
	'tzm' => 'tzm',
	'ug_CN' => 'ug',
	'uk' => 'uk',
	'ur' => 'ur',
	'uz_UZ' => 'uz',
	'vi' => 'vi',
	'wa' => 'wa',
	'xmf' => 'xmf',
	'zh_CN' => 'zh-cn',
	'zh_HK' => 'zh-hk',
	'zh_TW' => 'zh-tw',
}
reverse_langs = langs.invert
require 'pathname'
require 'open-uri'
plugin = Pathname.new(File.expand_path '../').basename
url_domain = 'http://translate.markjaquith.com'
url_path = '/projects/wordpress-plugins/'
item_url = url_domain + url_path + '%s/%s/default/export-translations?format=%s'
index_url = url_domain + url_path + '%s/'

formats = []
language_regex = %r{<a href="#{url_path}#{plugin}/([^/]+)/default"}

open( format index_url, plugin ) do |f|
	f.each_line do |l|
		if l.match language_regex
			lang = l[language_regex, 1]
			formats << [reverse_langs[lang], lang]
		end
	end
end

formats.each do |l|
	['po', 'mo'].each do |fmt|
		`wget -O #{plugin}-#{l[0]}.#{fmt} #{format item_url, plugin, l[1], fmt} 2>/dev/null`
	end
	`git checkout #{plugin}-#{l[0]}.*` if `git diff #{plugin}-#{l[0]}.po | ack '^[+-][^+-]{2}' | ack -v 'PO-Revision-Date'`.chomp.length === 0
end