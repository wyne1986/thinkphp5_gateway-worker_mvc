/*
Navicat MySQL Data Transfer

Source Server         : localhost
Source Server Version : 50505
Source Host           : localhost:3306
Source Database       : test

Target Server Type    : MYSQL
Target Server Version : 50505
File Encoding         : 65001

Date: 2018-04-18 18:05:04
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for tp_im_auth
-- ----------------------------
DROP TABLE IF EXISTS `tp_im_auth`;
CREATE TABLE `tp_im_auth` (
  `im_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `im_title` varchar(255) NOT NULL DEFAULT '' COMMENT '即时通信权限名称',
  `im_url` varchar(255) NOT NULL DEFAULT '' COMMENT '即时通信权限地址''控制器/方法''',
  `im_free` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否需要验证权限,0是,1否',
  `im_pid` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '分级父ID,按需使用',
  `add_time` int(11) NOT NULL DEFAULT '0',
  `del_time` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '软删除字段,1已删除',
  PRIMARY KEY (`im_id`),
  UNIQUE KEY `im_url` (`im_url`) USING BTREE,
  KEY `status` (`status`) USING BTREE,
  KEY `im_free` (`im_free`) USING BTREE,
  KEY `im_title` (`im_title`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COMMENT='即时通信接口权限表';

-- ----------------------------
-- Table structure for tp_im_role_auth
-- ----------------------------
DROP TABLE IF EXISTS `tp_im_role_auth`;
CREATE TABLE `tp_im_role_auth` (
  `role_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '角色ID',
  `im_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '即时通信权限ID',
  `role_type` varchar(255) NOT NULL DEFAULT '' COMMENT '角色分组分类,按需使用',
  PRIMARY KEY (`role_id`),
  UNIQUE KEY `role_id, role_type` (`role_id`,`role_type`) USING BTREE,
  KEY `role_id` (`role_id`) USING BTREE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='即时通信用户组权限关联表';

-- ----------------------------
-- Table structure for tp_user
-- ----------------------------
DROP TABLE IF EXISTS `tp_user`;
CREATE TABLE `tp_user` (
  `user_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `ucenter_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '用户中心ID·',
  `user_phone` varchar(50) NOT NULL DEFAULT '' COMMENT '用户电话',
  `user_pass` varchar(32) NOT NULL DEFAULT '',
  `user_salt` varchar(6) NOT NULL DEFAULT '123456',
  `user_logo` varchar(255) NOT NULL DEFAULT '' COMMENT '用户头像图片地址',
  `user_nickname` varchar(50) NOT NULL DEFAULT '' COMMENT '用户昵称',
  `user_realname` varchar(255) DEFAULT NULL COMMENT '用户真实姓名',
  `user_idcard` varchar(30) DEFAULT NULL COMMENT '用户身份证号码',
  `user_truename` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否已实名认证,0未认证',
  `user_role_id` int(11) unsigned NOT NULL DEFAULT '1' COMMENT '角色ID',
  `add_time` int(11) NOT NULL DEFAULT '0',
  `del_time` int(11) NOT NULL DEFAULT '0',
  `status` tinyint(1) unsigned NOT NULL DEFAULT '0',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `phone` (`user_phone`) USING BTREE,
  UNIQUE KEY `idcard` (`user_idcard`) USING BTREE,
  KEY `nickname` (`user_nickname`) USING BTREE,
  KEY `realname` (`user_realname`) USING BTREE,
  KEY `truename` (`user_truename`) USING BTREE,
  KEY `role_id` (`user_role_id`) USING BTREE,
  KEY `status` (`status`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=32 DEFAULT CHARSET=utf8;
