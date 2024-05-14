DELIMITER $$
CREATE PROCEDURE RecordRegister(
	IN In_SubjectId TEXT,
	IN In_Large2Small BOOLEAN,
	IN In_ImgPerms TEXT,
	IN In_TaskSets TEXT
)
BEGIN
IF (SELECT COUNT(SubjectId) FROM Register WHERE SubjectId=In_SubjectId)=0 THEN 
	INSERT INTO Register (
		SubjectId,
		Phase,
		Large2Small,
		ImgPerms,
		TaskSets
		) VALUES (
		In_SubjectId,
		0,
		In_Large2Small,
		In_ImgPerms,
		In_TaskSets);
END IF;
END$$
DELIMITER ;