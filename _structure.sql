-- phpMyAdmin SQL Dump
-- version 3.3.10
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: May 27, 2012 at 06:14 PM
-- Server version: 5.1.48
-- PHP Version: 5.3.8

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";

--
-- Database: `tuxion_wizard`
--

-- --------------------------------------------------------

--
-- Table structure for table `tx__wizard_link`
--

CREATE TABLE IF NOT EXISTS `tx__wizard_link` (
  `qnode_id` int(11) NOT NULL,
  `anode_id` int(11) NOT NULL,
  PRIMARY KEY (`qnode_id`,`anode_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `tx__wizard_nodes`
--

CREATE TABLE IF NOT EXISTS `tx__wizard_nodes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `qnode_id` int(11) DEFAULT NULL,
  `title` varchar(255) NOT NULL,
  `description` longtext NOT NULL,
  `url` varchar(255) NOT NULL,
  `url_target` varchar(8) NOT NULL,
  `breadcrumb` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=123 ;
