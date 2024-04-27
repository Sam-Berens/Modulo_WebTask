DELIMITER $$
CREATE PROCEDURE RecordTaskIO(
	IN In_AttemptId TEXT,
	IN In_SubjectId TEXT,
	IN In_SessionId INT,
	IN In_TrialId INT,
	IN In_PairId INT,
	IN In_TrialType TEXT,
	IN In_OppId INT,
	IN In_FeildIdx_A INT,
	IN In_FeildIdx_B INT,
	IN In_FeildIdx_C INT,
	IN In_AttemptNum INT,
	IN In_FeildIdx_R INT,
	IN In_Correct BOOLEAN,
	IN In_RT INT,
	IN In_DateTime_Write DATETIME
)
BEGIN
IF (SELECT COUNT(AttemptId) FROM TaskIO WHERE AttemptId=In_AttemptId)=0 THEN 
	INSERT INTO TaskIO (
		AttemptId,
		SubjectId,
		SessionId,
		TrialId,
		PairId,
		TrialType,
		OppId,
		FeildIdx_A,
		FeildIdx_B,
		FeildIdx_C,
		AttemptNum,
		FeildIdx_R,
		Correct,
		RT,
		DateTime_Write
		) VALUES (
		In_AttemptId,
		In_SubjectId,
		In_SessionId,
		In_TrialId,
		In_PairId,
		In_TrialType,
		In_OppId,
		In_FeildIdx_A,
		In_FeildIdx_B,
		In_FeildIdx_C,
		In_AttemptNum,
		In_FeildIdx_R,
		In_Correct,
		In_RT,
		In_DateTime_Write);
END IF;
END$$
DELIMITER ;