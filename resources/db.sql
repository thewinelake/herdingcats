create database hcat;

use hcat;

drop table if exists user;
create table user (
uid INTEGER NOT NULL AUTO_INCREMENT,
username varchar(50), -- this might be a nickname
crtgmt datetime,
status varchar(10),
statusgmt datetime,
email varchar(100) unique,
name varchar(100),
forename varchar(30),
surname varchar(30),
pwdhash varchar(80),
primary key (uid)
);

drop table if exists address;
create table address (
uid INTEGER,
addrtype varchar(10),
address varchar(100),
addruse varchar(10),
primary key (uid,address)
);

drop table if exists event;
create table event (
eid INTEGER NOT NULL AUTO_INCREMENT,
owneruid INTEGER,
title varchar(100),
date varchar(30),
agreedgmt datetime,
descriptionmid integer,
primary key (eid)
);

drop table if exists invitation;
create table invitation (
eid INTEGER,
uid INTEGER, -- Future use?
address varchar(100),
ikey varchar(20),
name varchar(100),
status varchar(10),
crtgmt datetime,
statusgmt datetime,
descriptionmid integer,
primary key (eid,address)
);

drop table if exists uurelationship;
create table uurelationship (
uidA INTEGER,
uidB INTEGER,
relationship varchar(10),
status varchar(10),
primary key (uidA,uidB)
);

drop table if exists usergroup;
create table usergroup (
gid INTEGER NOT NULL AUTO_INCREMENT,
owneruid INTEGER,
groupname varchar(20),
primary key (gid)
);

drop table if exists usergroupmembership;
create table usergroupmembership (
gid INTEGER,
uid INTEGER,
crtgmt datetime,
status varchar(10),
primary key (gid,uid)
);

drop table if exists message;
create table message (
mid INTEGER NOT NULL AUTO_INCREMENT,
eid INTEGER,
uid INTEGER,
gmt datetime,
parentmid INTEGER,
msgtext text,
msghtml text,
primary key (mid)
);


create user 'garfield'@'%' identified by 'JimDavis';

grant all on hcat.* to 'garfield'@'%';