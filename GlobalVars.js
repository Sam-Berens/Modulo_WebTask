// Session specific
var Phase = 0;
var FieldSize = 7;
var SessionId = 78;
var ImgPerm = [4, 2, 3, 1, 0, 5, 6];
var TimelineVars = GetTimelineVars();
var ArrayOfResponseTags = GetArrayOfResponseTags();
var Ptarget = TimelineVars.map((o)=>o.Ptarget);
var TrainHistory;

// Trial specific
var TrialId = -1;
var CurrentQuestion = {};
var StartTime_ResponsePrompt = NaN;
var AttemptNum = -1;
var AcceptResponse = true;