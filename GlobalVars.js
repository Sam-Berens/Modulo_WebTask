// Session specific
var SessionId;
var FieldSize;
var ImgPerm;
var TaskSet;
var Ptarget;
var ArrayOfResponseTags;
var TrainHistory;
var BiasQ = false; // Bias the sampling distribution of pairs to rapidly return to Ptarget;
var Img2ScreenRatio = 0.27;

// Trial specific
var TrialId = -1;
var CurrentQuestion = {};
var StartTime_ResponsePrompt = NaN;
var AttemptNum = -1;
var AcceptResponse = true;
var ImgSize = 300;