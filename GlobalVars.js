// Session specific
var FieldPermutation = [4, 2, 3, 1, 0, 5, 6];
var SessionId = 78;
var ArrayOfResponseTags = GetArrayOfResponseTags();
var TimelineVars = GetTimelineVars();
var Ptarget = TimelineVars.map((o)=>o.Ptarget);
var TrainHistory;

// Trial specific
var TrialId = -1;
var CurrentQuestion = {};
var StartTime_ResponsePrompt = NaN;
var AttemptNum = -1;
var AcceptResponse = true;