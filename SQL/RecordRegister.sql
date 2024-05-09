DELIMITER $$
CREATE PROCEDURE RecordRegister(
	IN In_SubjectId TEXT,
	IN In_Large2Small BOOLEAN,
	IN In_FieldPerms TEXT,
	IN In_TaskSets TEXT
)
BEGIN
IF (SELECT COUNT(SubjectId) FROM Register WHERE SubjectId=In_SubjectId)=0 THEN 
	INSERT INTO Register (
		SubjectId,
		Phase,
		Large2Small,
		FieldPerms,
		TaskSets
		) VALUES (
		In_SubjectId,
		0,
		In_Large2Small,
		In_FieldPerms,
		In_TaskSets);
END IF;
END$$
DELIMITER ;