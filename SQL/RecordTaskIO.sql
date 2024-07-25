DELIMITER $$
CREATE PROCEDURE RecordTaskIO(
	IN In_AttemptId TEXT,
	IN In_SubjectId TEXT,
	IN In_FieldSize INT,
	IN In_SessionId INT,
	IN In_TrialId INT,
	IN In_PairId INT,
	IN In_TrialType TEXT,
	IN In_OppId INT,
	IN In_FieldIdx_A INT,
	IN In_FieldIdx_B INT,
	IN In_FieldIdx_C INT,
	IN In_AttemptNum INT,
	IN In_FieldIdx_R INT,
	IN In_Correct BOOLEAN,
	IN In_RT INT,
	IN In_DateTime_Write DATETIME
)
BEGIN
IF (SELECT COUNT(AttemptId) FROM TaskIO WHERE AttemptId=In_AttemptId)=0 THEN 
	INSERT INTO TaskIO (
		AttemptId,
		SubjectId,
		FieldSize,
		SessionId,
		TrialId,
		PairId,
		TrialType,
		OppId,
		FieldIdx_A,
		FieldIdx_B,
		FieldIdx_C,
		AttemptNum,
		FieldIdx_R,
		Correct,
		RT,
		DateTime_Write
		) VALUES (
		In_AttemptId,
		In_SubjectId,
		In_FieldSize,
		In_SessionId,
		In_TrialId,
		In_PairId,
		In_TrialType,
		In_OppId,
		In_FieldIdx_A,
		In_FieldIdx_B,
		In_FieldIdx_C,
		In_AttemptNum,
		In_FieldIdx_R,
		In_Correct,
		In_RT,
		In_DateTime_Write);
END IF;
END$$
DELIMITER ;