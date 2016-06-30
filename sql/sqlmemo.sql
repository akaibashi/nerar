select
2 as division, -- 画像の投稿
own_u.nick as own_nick,
null as nick,
file_name as file_name,
comment as comment,
created as created
from
image_info as i
left outer join
users as own_u
on
i.user_id = own_u.id
where
i.user_id in(2,3)
union
select
1 as division, -- 誰かの画像へのコメント
own_u.nick as own_nick,
u.nick as nick,
i.file_name as file_name,
h.comment as comment,
h.created as created
from
hint as h
inner join
image_info as i
on
h.image_id = i.id
left outer join
users as u
on
i.user_id = u.id
left outer join
users as own_u
on
h.user_id = own_u.id
where
h.user_id in(2,3)
order by created desc
limit 20
;