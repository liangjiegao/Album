create table user_option(
    id      int(11)         auto_increment primary key ,
    search  varchar(32)     not null    default '' comment '搜索内容',
    ip      varchar(32)     not null    default '' comment '搜索的ip',
    user_id int(11)         null        default 0  comment '用户id',
    create_time int(11)     not null    default 0   comment '创建时间'
)charset = utf8;
