-- 用户信息
create table user_info(
          id              int(11)     auto_increment primary key  comment '主键',
          account         char(32)    unique not null default ''  comment '账号',
          phone           char(32)    null default ''             comment '电话号码',
          email           char(32)    null default ''             comment '邮箱',
          birthday        char(32)    null default ''             comment '生日',
          icon            char(128)   null default ''             comment '头像',
          remark          varchar(1024) null default ''           comment '备注',
          create_time     int(11) not null default 0          comment '创建时间',
          last_login_time int(11) null default 0          comment '最后一次登录时间',
          last_login_ip   char(32) null default ''        comment '最后一次登录ip'
)ENGINE=InnoDB AUTO_INCREMENT=3150 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
ALTER TABLE user_info add column password char(32) not null default '';
ALTER TABLE user_info add column nickname char(32) not null default '';
alter table user_info modify nickname char(32) default '' null;

-- 图片信息sd
create table img_info(
                         id              int(11)     auto_increment primary key  comment '主键',
                         img_key         char(32)    not null unique default ''  comment '唯一键',
                         account         char(32)    not null default ''  comment '所属人账号',
                         share_level     tinyint     not null default 0          comment '保密等级 0 保留， 1 私有， 2 好友间分享， 3 全部人共享',
                         path            char(128)   not null default ''         comment '图片路径',
                         creat_time      int(11)     not null default 0          comment '创建时间'
)ENGINE=InnoDB AUTO_INCREMENT=3150 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- 图片标签
create table img_tag(
                        id              int(11)     auto_increment primary key  comment '主键',
                        tag_key         char(32)    not null default ''  comment '唯一键',
                        img_key         char(32)    not null default ''         comment '图片key',
                        creat_time      int(11)     not null default 0          comment '创建时间'
)ENGINE=InnoDB AUTO_INCREMENT=3150 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- 标签库
create table tag(
                    id              int(11)     auto_increment primary key  comment '主键',
                    tag_key         char(32)    not null unique default ''  comment '唯一键',
                    name            char(32)    not null default ''         comment '标签名',
                    creat_time      int(11)     not null default 0          comment '创建时间'
)ENGINE=InnoDB AUTO_INCREMENT=3150 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- 用户间关系
create table user_relation(
                              id              int(11)     auto_increment primary key  comment '主键',
                              relation_key    char(32)    not null unique default ''  comment '唯一键',
                              account_self    char(32)    not null default ''         comment '自己的账号',
                              account_friend  char(32)    not null default ''         comment '好友账号',
                              creat_time      int(11)     not null default 0          comment '创建时间'
)ENGINE=InnoDB AUTO_INCREMENT=3150 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- 分享信息表
create table share_info(
                           id              int(11)         auto_increment primary key  comment '主键',
                           share_key       char(32)        not null unique default ''  comment '唯一键',
                           account         char(32)        not null default ''         comment '自己的账号',
                           img_key         varchar(1024)   null default ''             comment '图片',
                           info            varchar(1024)   null default ''             comment '文字',
                           share_group     json            null                        comment '可见用户 若为空，则需要通过图片可见性进行判断',
                           addr            char(128)       null default ''             comment '位置',
                           creat_time      int(11)         not null default 0          comment '创建时间'
)ENGINE=InnoDB AUTO_INCREMENT=3150 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- 评论
create table comment(
                        id              int(11)         auto_increment primary key  comment '主键',
                        comment_key     char(32)        not null unique default ''  comment '唯一键',
                        comment_info    varchar(1024)   not null default ''         comment '评论内容',
                        pid_first       int(11)         null default 0              comment '一级父id',
                        pid_second      int(11)         null default 0              comment '二级父id',
                        account         char(32)        not null default ''         comment '账号',
                        create_time     int(11)         not null default 0          comment '创建时间'
)ENGINE=InnoDB AUTO_INCREMENT=3150 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;


alter table user_info modify birthday datetime null comment '生日';

