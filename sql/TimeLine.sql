SELECT 
	division
	,inick
	,hnick
	,file_name
	,COMMENT
	,created
FROM
	/* image_info main */
	((
		SELECT
			2 AS division
			,ui.nick AS inick
			,NULL AS hnick
			,i.file_name AS file_name
			,i.comment AS COMMENT
			,i.created AS created
		FROM
			image_info AS i	
			INNER JOIN 
			users AS ui ON i.user_id = ui.id
		WHERE
			i.user_id IN(2,3)
		ORDER BY created DESC
		LIMIT 20
	)
	UNION
	/* hint main */
	(
		SELECT
			1 AS division
			,ui.nick AS inick
			,uh.nick AS hnick
			,i.file_name AS file_name
			,h.comment AS COMMENT
			,h.created AS created
		FROM
			hint AS h 
			INNER JOIN 
			users AS uh ON h.user_id = uh.id
			INNER JOIN 
			image_info AS i ON h.image_id = i.id
			INNER JOIN 
			users AS ui ON i.user_id = ui.id
		WHERE
			h.user_id IN(2,3)
		ORDER BY created DESC
		LIMIT 20
	)) AS tmp
ORDER BY created
LIMIT 20;