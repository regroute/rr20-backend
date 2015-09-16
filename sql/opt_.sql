-- --------------------------------------------------------
-- Хост:                         127.0.0.1
-- Версия сервера:               5.5.43-0ubuntu0.14.04.1 - (Ubuntu)
-- ОС Сервера:                   debian-linux-gnu
-- HeidiSQL Версия:              9.1.0.4867
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8mb4 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;

-- Дамп структуры базы данных rr20
CREATE DATABASE IF NOT EXISTS `rr20` /*!40100 DEFAULT CHARACTER SET utf8 */;
USE `rr20`;


-- Дамп структуры для таблица rr20.cc_options
CREATE TABLE IF NOT EXISTS `cc_options` (
  `id_options` int(11) NOT NULL AUTO_INCREMENT,
  `name_options` varchar(255) NOT NULL,
  `options` varchar(255) NOT NULL,
  `how_to` tinytext NOT NULL COMMENT 'Как выбираем транк',
  `group_options` varchar(50) NOT NULL,
  `note_options` tinytext NOT NULL,
  PRIMARY KEY (`id_options`)
) ENGINE=InnoDB AUTO_INCREMENT=76 DEFAULT CHARSET=utf8;

-- Дамп данных таблицы rr20.cc_options: ~75 rows (приблизительно)
DELETE FROM `cc_options`;
/*!40000 ALTER TABLE `cc_options` DISABLE KEYS */;
INSERT INTO `cc_options` (`id_options`, `name_options`, `options`, `how_to`, `group_options`, `note_options`) VALUES
	(1, 'verbosity_level', '5', '0 = FATAL; 1 = ERROR; WARN = 2 ; INFO = 3 ; DEBUG = 4 ', 'agi', 'Уровень логов в консоли 5 все 0 ничего'),
	(2, 'logging_write_file', '1', '0 OR 1', 'agi', 'Писать или нет лог в файл'),
	(3, 'mixmon_post', 'ab', 'a,b', 'agi', 'Ключи в команде MixMonitir'),
	(4, 'monitor_formatfile', 'gsm', 'gsm|wav|mp3', 'agi', 'Формат записи'),
	(5, 'failover_recursive_limit', '12', '1...10', 'agi', 'Сколько транков перебираем'),
	(6, 'record_call_out', '0', '0 OR 1', 'agi', 'Записывать разговоры исход'),
	(7, 'record_call_in', '0', '0 OR 1', 'agi', 'Записывать разговоры вход'),
	(8, 'record_call_local', '0', '0 OR 1', 'agi', 'Записывать разговоры вход'),
	(9, 'monitor_path_out', '/var/spool/asterisk/monitor/', '/var/spool/asterisk/monitor/', 'agi', 'Директория для сохранения записей'),
	(10, 'monitor_path_in', '/var/spool/asterisk/monitor/', '/var/spool/asterisk/monitor/', 'agi', 'Директория для сохранения записей вход'),
	(11, 'monitor_path_local', '/var/spool/asterisk/monitor/', '/var/spool/asterisk/monitor/', 'agi', 'Директория для сохранения записей вход'),
	(12, 'dialcommand_param_out', ',120,Tt(%timeout%:61000:30000)', ',120,Tt,S(%timeout%) | ,120,Tt(%timeout%:61000:30000)', 'agi', 'Строка набора исход'),
	(13, 'dialcommand_param_in', ',90,TtD(%timeout%:61000:30000)', ',120,Tt,S(%timeout%) | ,240,TtD(%timeout%:61000:30000)', 'agi', 'Строка набора dial вход'),
	(14, 'dialcommand_param_local', ',30,Tt(%timeout%:61000:30000)', ',120,Tt,S(%timeout%) | ,240,Tt(%timeout%:61000:30000)', 'agi', 'Строка набора dial вход'),
	(15, 'maxlong_call_in', '3600', '600-3600', 'agi', 'Max time to Call a DID no billed '),
	(16, 'maxlong_call_local', '1800', '600-3600', 'agi', 'Max time to Call a DID no billed '),
	(17, 'stoperror_context', 'Congestion', 'Congestion OR  Busy', 'agi', 'Что делаем при ошибке '),
	(18, 'stoperror_extension', '1', '20', 'agi', 'Код ошибки '),
	(19, 'min_dial', '600', '600 sec /5 мин', 'agi', 'Сколько секунд должно быть на транке чтобы через него был розрешен набор.'),
	(20, 'max_dial', '1800', '1800 sec /0,5 час| 3600 sec /1 час', 'agi', 'Максимально возможная длительность разговора.'),
	(21, 'switchdialcommand', '0', '0 OR 1', 'agi', 'Варианты набора при команде dial'),
	(22, 'ACTION_CHANUNAVAIL', 'continue', 'continue|return', 'agi', 'Действие при статусе CHANUNAVAIL'),
	(23, 'ACTION_CONGESTION', 'continue', 'continue|return', 'agi', 'Действие при статусе CONGESTION'),
	(24, 'ACTION_BUSY', 'return', 'continue|return', 'agi', 'Действие при статусе BUSY'),
	(25, 'ACTION_CANCEL', 'return', 'continue|return', 'agi', 'Действие при статусе CANCEL'),
	(26, 'ACTION_NOANSWER', 'return', 'continue|return', 'agi', 'Действие при статусе NOANSWER'),
	(27, 'nomer_regular', '^([0-9_]){2,15}$', '^([0-9_]){2,15}$', 'blacklist', 'Верификация в Web консоли.'),
	(28, 'cid_regular', '^((\\\\\')(([0-9A-Z-_a-z])+)([\'<]+)(\\\\d{8,12})(\\\\>)|)$', '^((\\\\\')(([0-9A-Z-_a-z])+)([\'<]+)(\\\\d{8,12})(\\\\>)|)$', 'customer', 'Верификация в Web консоли.'),
	(29, 'context_regular', '^([0-9A-Z-_a-z]){3,50}$', '^([0-9A-Z-_a-z]){3,50}$', 'directions', 'Верификация в Web консоли.'),
	(30, 'pchars_min', '12', '3-15', 'iaxakk', ''),
	(31, 'default_permit', '192.168.1.0/255.255.255.0', '192.168.1.0/255.255.255.0', 'iaxakk', ''),
	(32, 'pchars_max', '12', '3-15', 'iaxakk', ''),
	(33, 'use_upper_case', '1', '1 OR 0', 'iaxakk', ''),
	(34, 'default_deny', '0.0.0.0/0.0.0.0', '0.0.0.0/0.0.0.0', 'iaxakk', ''),
	(35, 'pin_char', '1', '1 OR 0', 'iaxakk', ''),
	(36, 'default_qualify', 'no', 'no OR yes', 'iaxakk', ''),
	(37, 'pin_spe_chars', '0', '1 OR 0', 'iaxakk', ''),
	(38, 'default_disallow', 'ALL', 'need to disallow=all before we can use allow. ( default : all )   ', 'iaxakk', ''),
	(39, 'default_allow', 'alaw,ulaw', 'Set allow codecs separated by a comma, e.g. gsm,alaw,ulaw ( default : ulaw,alaw,gsm,g729)   ', 'iaxakk', ''),
	(40, 'default_context', 'out-internal', 'to_trunk', 'iaxakk', ''),
	(41, 'default_type', 'friend', 'type = friend | peer | user ( default :user  )  ', 'iaxakk', ''),
	(42, 'nchars_min', '12', '5-15', 'iaxakk', ''),
	(43, 'nchars_max', '12', '5-15', 'iaxakk', ''),
	(44, 'nin_char', '0', '1 OR 0', 'iaxakk', ''),
	(45, 'default_trunk', 'no', 'no OR yes', 'iaxakk', ''),
	(46, 'default_amaflags', 'billing', 'billing', 'iaxakk', ''),
	(47, 'default_language', 'RU', 'RU', 'iaxakk', ''),
	(48, 'cid_regular', '^((\\\\\')(([0-9A-Z-_a-z])+)([\'<]+)(\\\\d{8,12})(\\\\>)|)$', '^((\\\\\')(([0-9A-Z-_a-z])+)([\'<]+)(\\\\d{8,12})(\\\\>)|)$', 'iaxakk', 'Верификация в Web консоли.'),
	(49, 'pchars_min', '12', '3-15', 'sipakk', ''),
	(50, 'default_permit', '172.168.1.0/255.255.255.0', '192.168.1.0/255.255.255.0', 'sipakk', ''),
	(51, 'pchars_max', '12', '3-15', 'sipakk', ''),
	(52, 'use_upper_case', '1', '1 OR 0', 'sipakk', ''),
	(53, 'default_deny', '0.0.0.0/0.0.0.0', '0.0.0.0/0.0.0.0', 'sipakk', ''),
	(54, 'pin_char', '1', '1 OR 0', 'sipakk', ''),
	(55, 'default_qualify', 'no', 'no OR yes', 'sipakk', ''),
	(56, 'pin_spe_chars', '0', '1 OR 0', 'sipakk', ''),
	(57, 'default_amaflags', 'billing', 'billing', 'sipakk', ''),
	(58, 'default_disallow', 'ALL', 'need to disallow=all before we can use allow. ( default : all )   ', 'sipakk', ''),
	(59, 'default_allow', 'alaw,ulaw', 'Set allow codecs separated by a comma, e.g. gsm,alaw,ulaw ( default : ulaw,alaw,gsm,g729)   ', 'sipakk', ''),
	(60, 'default_context', 'out-internal', 'to_trunk', 'sipakk', ''),
	(61, 'default_nat', 'no', 'nat = yes | no | never | route ( default :no )   ', 'sipakk', ''),
	(62, 'default_dtmfmode', 'RFC2833 ', 'dtmfmode = RFC2833 | INFO | INBAND | AUTO ( default : RFC2833 )   ', 'sipakk', ''),
	(63, 'default_type', 'friend', 'type = friend | peer | user ( default :user  )  ', 'sipakk', ''),
	(64, 'default_canreinvite', 'no', 'canreinvite : yes | no ( default : no )   ', 'sipakk', ''),
	(65, 'default_cancallforward', 'yes', 'cancallforward = yes | no ( default : yes )   ', 'sipakk', ''),
	(66, 'nchars_min', '12', '5-15', 'sipakk', ''),
	(67, 'nchars_max', '12', '5-15', 'sipakk', ''),
	(68, 'nin_char', '0', '1 OR 0', 'sipakk', ''),
	(69, 'default_language', 'RU', 'RU', 'sipakk', ''),
	(70, 'cid_regular', '^((\\\\\')(([0-9A-Z-_a-z])+)([\'<]+)(\\\\d{8,12})(\\\\>)|)$', '^((\\\\\')(([0-9A-Z-_a-z])+)([\'<]+)(\\\\d{8,12})(\\\\>)|)$', 'sipakk', 'Верификация в Web консоли.'),
	(71, 'name_regular', '^([a-zA-Z0-9_]){3,25}$', '^([a-zA-Z0-9_]){6,15}$', 'supertrunk', 'Верификация в Web консоли.'),
	(72, 'ext_regular', '^([a-zA-Z0-9_]){6,15}$', '^([a-zA-Z0-9_]){6,15}$', 'trunkout', 'Верификация в Web консоли.'),
	(73, 'trunk_regular', '^([a-zA-Z0-9_]){6,15}$', '^([a-zA-Z0-9_]){6,15}$', 'trunkout', 'Верификация в Web консоли.'),
	(74, 'cid_regular', '^((\\\\\')(([0-9A-Z-_a-z])+)([\'<]+)(\\\\d{8,12})(\\\\>)|)$', '^((\\\\\')(([0-9A-Z-_a-z])+)([\'<]+)(\\\\d{8,12})(\\\\>)|)$', 'trunkout', 'Верификация в Web консоли.'),
	(75, 'nomer_regular', '^([0-9_]){2,15}$', '^([0-9_]){2,15}$', 'whitelist', 'Верификация в Web консоли.');
/*!40000 ALTER TABLE `cc_options` ENABLE KEYS */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IF(@OLD_FOREIGN_KEY_CHECKS IS NULL, 1, @OLD_FOREIGN_KEY_CHECKS) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
