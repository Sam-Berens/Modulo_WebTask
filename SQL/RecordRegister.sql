DELIMITER $$
CREATE PROCEDURE RecordRegister(
	IN In_SubjectId TEXT,
	IN In_ImgPerm TEXT,
	IN In_TaskSet TEXT
)
BEGIN
IF (SELECT COUNT(SubjectId) FROM Register WHERE SubjectId=In_SubjectId)=0 THEN 
	INSERT INTO Register (
		SubjectId,
		ImgPerm,
		TaskSet
		) VALUES (
		In_SubjectId,
		In_ImgPerm,
		In_TaskSet);
END IF;
END$$
DELIMITER ;