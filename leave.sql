-- phpMyAdmin SQL Dump
-- version 4.5.4.1deb2ubuntu2
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: 2016-12-19 20:19:44
-- 服务器版本： 5.7.15-0ubuntu0.16.04.1
-- PHP Version: 7.0.8-0ubuntu0.16.04.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `leave`
--

-- --------------------------------------------------------

--
-- 表的结构 `employee`
--

CREATE TABLE `employee` (
  `employee_id` bigint(64) NOT NULL,
  `employee_name` varchar(64) DEFAULT NULL,
  `employee_password` varchar(32) NOT NULL,
  `employee_gender` tinyint(1) DEFAULT NULL,
  `employee_birthday` date DEFAULT NULL,
  `employee_entrydate` date DEFAULT NULL,
  `employee_department` varchar(64) DEFAULT NULL,
  `employee_path` varchar(256) NOT NULL,
  `employee_token` varchar(128) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `employee`
--

INSERT INTO `employee` (`employee_id`, `employee_name`, `employee_password`, `employee_gender`, `employee_birthday`, `employee_entrydate`, `employee_department`, `employee_path`, `employee_token`) VALUES
(2016, 'employee', '123456', 0, '1995-12-31', '2014-12-20', '人事部', '0/1', '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `employee`
--
ALTER TABLE `employee`
  ADD PRIMARY KEY (`employee_id`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `employee`
--
ALTER TABLE `employee`
  MODIFY `employee_id` bigint(64) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12017;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
