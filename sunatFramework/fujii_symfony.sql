-- phpMyAdmin SQL Dump
-- version 4.4.15
-- http://www.phpmyadmin.net
--
-- Host: db02.local.dev.sunws.net
-- Generation Time: 2019 年 4 月 18 日 18:06
-- サーバのバージョン： 5.5.46
-- PHP Version: 5.5.29

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `fujii_symfony`
--

-- --------------------------------------------------------

--
-- テーブルの構造 `article`
--

CREATE TABLE IF NOT EXISTS `article` (
  `id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `title` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `description` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `contents` longtext COLLATE utf8_unicode_ci,
  `image` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `date` date NOT NULL,
  `writer_id` int(11) NOT NULL,
  `imgDel` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB AUTO_INCREMENT=52 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- テーブルのデータのダンプ `article`
--

INSERT INTO `article` (`id`, `category_id`, `title`, `description`, `contents`, `image`, `date`, `writer_id`, `imgDel`) VALUES
(7, 1, 'この投稿は違うよ2', '投この投稿は違うよこの投稿は違うよこの投稿は違うよ', '<h2>この投稿は違うよ</h2>\r\n<p>この投稿は違うよこの投稿は違うよこの投稿は違うよこの投稿は違うよこの投稿は違うよこの投稿は違うよ</p>', '/upload/40665dbfba96d5e8b07edc7470c4918b.jpg', '2018-04-10', 1, 0),
(15, 4, 'テスト投稿です', 'この投稿はテストです。', '<h2>この投稿はテストです。</h2>\r\n<p>この投稿はテストです。この投稿はテストです。この投稿はテストです。この投稿はテストです。この投稿はテストです。この投稿はテストです。この投稿はテストです。この投稿はテストです。この投稿はテストです。この投稿はテストです。この投稿はテストです。この投稿はテストです。この投稿はテストです。この投稿はテストです。この投稿はテストです。この投稿はテストです。この投稿はテストです。この投稿はテストです。この投稿はテストです。この投稿はテストです。</p>', '/uploads/20190411163438.jpg', '1960-10-14', 5, 0),
(17, 2, 'ニュースの投稿です', 'ニュースの投稿ですニュースの投稿です', '<h2>ニュースの投稿です</h2>\r\n<p>ニュースの投稿ですニュースの投稿ですニュースの投稿ですニュースの投稿ですニュースの投稿ですニュースの投稿ですニュースの投稿です</p>', '/uploads/20190411172847.jpg', '2016-03-10', 3, 0),
(25, 1, 'この投稿は違うよ', '投この投稿は違うよこの投稿は違うよこの投稿は違うよ', '<h2>この投稿は違うよ</h2>\r\n<p>この投稿は違うよこの投稿は違うよこの投稿は違うよこの投稿は違うよこの投稿は違うよこの投稿は違うよ</p>', '/uploads/20190411162132.jpg', '2018-04-10', 5, 0),
(27, 4, 'この投稿は違うよ', '投この投稿は違うよこの投稿は違うよこの投稿は違うよ', '<h2>この投稿は違うよ</h2>\r\n<p>この投稿は違うよこの投稿は違うよこの投稿は違うよこの投稿は違うよこの投稿は違うよこの投稿は違うよ</p>', NULL, '2018-04-10', 1, 0),
(32, 1, 'この投稿は違うよ', '投この投稿は違うよこの投稿は違うよこの投稿は違うよ', '<h2>この投稿は違うよ</h2>\r\n<p>この投稿は違うよこの投稿は違うよこの投稿は違うよこの投稿は違うよこの投稿は違うよこの投稿は違うよ</p>', NULL, '2018-04-10', 1, 0),
(37, 1, 'この投稿は違うよ', '投この投稿は違うよこの投稿は違うよこの投稿は違うよ', '<h2>この投稿は違うよ</h2>\r\n<p>この投稿は違うよこの投稿は違うよこの投稿は違うよこの投稿は違うよこの投稿は違うよこの投稿は違うよ</p>', '/uploads/20190411163308.jpg', '2018-04-10', 1, 0),
(39, 1, 'この投稿は違うよ', '投この投稿は違うよこの投稿は違うよこの投稿は違うよ', '<h2>この投稿は違うよ</h2>\r\n<p>この投稿は違うよこの投稿は違うよこの投稿は違うよこの投稿は違うよこの投稿は違うよこの投稿は違うよ</p>', '/uploads/20190411162132.jpg', '2018-04-10', 5, 0),
(42, 2, 'この投稿は違うよ', '投この投稿は違うよこの投稿は違うよこの投稿は違うよ', '<h2>この投稿は違うよ</h2>\r\n<p>この投稿は違うよこの投稿は違うよこの投稿は違うよこの投稿は違うよこの投稿は違うよこの投稿は違うよ</p>', '/uploads/20190417120102.jpg', '2018-05-05', 1, 0),
(44, 1, 'この投稿は違うよ', '投この投稿は違うよこの投稿は違うよこの投稿は違うよ', '<h2>この投稿は違うよ</h2>\r\n<p>この投稿は違うよこの投稿は違うよこの投稿は違うよこの投稿は違うよこの投稿は違うよこの投稿は違うよ</p>', '/upload/a3026a17cc263be89c52dc739efebd3c.jpg', '2018-04-10', 5, 0),
(45, 1, 'この投稿は同じ', '投この投稿は違うよこの投稿は違うよこの投稿は違うよ', '<h2>この投稿は違うよ</h2>\r\n<p>この投稿は違うよこの投稿は違うよこの投稿は違うよこの投稿は違うよこの投稿は違うよこの投稿は違うよ</p>', NULL, '2018-04-10', 1, 0),
(47, 1, 'symfony製ではありません。', 'symfony製ではありません。symfony製ではありません。symfony製ではありません。symfony製ではありません。symfony製ではありません。symfony製ではありません。symfony製ではありません。symfony製ではありません。symfony製ではありません。symfony製ではありません。symfony製ではありません。symfony製ではありません。symfony製ではありません。', '<h2>symfony製ではありません。</h2>\r\n<p>symfony製ではありません。symfony製ではありません。symfony製ではありません。symfony製ではありません。symfony製ではありません。symfony製ではありません。symfony製ではありません。symfony製ではありません。symfony製ではありません。</p>\r\n<p>symfony製ではありません。symfony製ではありません。symfony製ではありません。symfony製ではありません。</p>', '/uploads/20190411174105.jpg', '2010-04-11', 1, 0),
(48, 3, 'シンフォニーではない投稿4', 'シンフォニーではない投稿4シンフォニーではない投稿4シンフォニーではない投稿4シンフォニーではない投稿4シンフォニーではない投稿4シンフォニーではない投稿4シンフォニーではない投稿4', '<h2>シンフォニーではない投稿4</h2>', NULL, '2019-04-11', 1, 0),
(49, 1, 'シンフォニーではない投稿', 'シンフォニーではない投稿シンフォニーではない投稿シンフォニーではない投稿', 'シンフォニーではない投稿シンフォニーではない投稿シンフォニーではない投稿シンフォニーではない投稿シンフォニーではない投稿シンフォニーではない投稿シンフォニーではない投稿シンフォニーではない投稿シンフォニーではない投稿シンフォニーではない投稿シンフォニーではない投稿シンフォニーではない投稿', NULL, '2019-04-11', 1, 0),
(50, 1, 'シンフォニーではない投稿6', 'シンフォニーではない投稿6シンフォニーではない投稿6シンフォニーではない投稿6シンフォニーではない投稿6シンフォニーではない投稿6シンフォニーではない投稿6シンフォニーではない投稿6シンフォニーではない投稿6シンフォニーではない投稿6シンフォニーではない投稿6シンフォニーではない投稿6', 'シンフォニーではない投稿6シンフォニーではない投稿6シンフォニーではない投稿6シンフォニーではない投稿6シンフォニーではない投稿6', '/uploads/20190411180818.jpg', '2019-04-11', 1, 0),
(51, 3, 'シンフォニーでないです', 'シンフォニーでないですシンフォニーでないですシンフォニーでないです。', '<h2>シンフォニーではないんですよ。</h2>\r\n<p>シンフォニーではなくて、phpです。</p>', NULL, '2020-05-05', 1, 0);

-- --------------------------------------------------------

--
-- テーブルの構造 `category`
--

CREATE TABLE IF NOT EXISTS `category` (
  `id` int(11) NOT NULL,
  `category_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- テーブルのデータのダンプ `category`
--

INSERT INTO `category` (`id`, `category_name`) VALUES
(1, 'ブログ'),
(2, 'ニュース'),
(3, 'コラム'),
(4, 'テスト');

-- --------------------------------------------------------

--
-- テーブルの構造 `writer`
--

CREATE TABLE IF NOT EXISTS `writer` (
  `id` int(11) NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `profile` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `image` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `imgDel` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- テーブルのデータのダンプ `writer`
--

INSERT INTO `writer` (`id`, `name`, `profile`, `image`, `imgDel`) VALUES
(1, 'テスト太郎他', 'テスト太郎に改名しました。', '', 0),
(3, 'テスト小太郎', 'テスト小太郎ですー', '/upload/d29f8defe1d58d774b1a355632c4174e.jpg', 0),
(5, 'テスト又三郎', 'てすと', '/upload/d1ea94131603d8d9f63400603a786942.jpg', 0),
(6, 'テスト又二郎', 'テスト又二郎です。', '/upload/322497a904e4d487aeaf14f7240ba1a6.jpg', 0),
(7, 'てすと', '西岡テスト', '', 0);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `article`
--
ALTER TABLE `article`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `category`
--
ALTER TABLE `category`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `writer`
--
ALTER TABLE `writer`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `article`
--
ALTER TABLE `article`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=52;
--
-- AUTO_INCREMENT for table `category`
--
ALTER TABLE `category`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `writer`
--
ALTER TABLE `writer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=8;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
