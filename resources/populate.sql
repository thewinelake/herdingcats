insert into user (username,crtgmt,status,statusgmt,forename,surname,pwdhash)
values (
'alexlake',
'2017-05-08 20:20',
'active',
'2017-05-08 20:20',
'Alex',
'Lake',
'indigo'
);

insert into address (uid,addrtype,address,addruse) values (
1,
'email',
'alex@thewinelake.com',
'primary'
);

insert into event(
owneruid,
title,
agreedgmt,
descriptionmid ) values (
1,
'fish and chips offline',
'2017-05-25',
1
);

insert into message (msgtext,msghtml) values (
'fish, chips and vino',
'fish, <i>chips</i> and <b>vino</b>',
);