// Helper function: Sleep
function Sleep(ms) {
	return new Promise(resolve => setTimeout(resolve, ms));
}

// Function to convert FieldIdx to filenames
function FieldIdx2ImgName(FieldIdx) {
	var ImgName = './Imgs/i' + ImgPerm[FieldIdx].toString().padStart(2, '0') + '.png';
	return ImgName;
}

// Function to get the SessionId, FieldSize, ImgPerm, and TaskSet
async function GetSessionDef() {

	var DataToSend = {};
	DataToSend.SubjectId = SubjectId;

	//Send data to php script
	var P1 = await fetch('./GetSessionDef.php', {
		method: 'post',
		headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' },
		body: JSON.stringify(DataToSend)
	});

	var SessionDef = await P1.json();
	SessionId = SessionDef.SessionId;
	FieldSize = SessionDef.FieldSize;
	ImgPerm = SessionDef.ImgPerm;
	TaskSet = SessionDef.TaskSet;
	Ptarget = TaskSet.map((o)=>o.Ptarget);

	// Add Fn_A, Fn_B, and Fn_C to each element of the task set
	for (var ii = 0; ii < Math.pow(FieldSize,2); ii++) {
		TaskSet[ii].Fn_A = FieldIdx2ImgName(TaskSet[ii].FieldIdx_A);
		TaskSet[ii].Fn_B = FieldIdx2ImgName(TaskSet[ii].FieldIdx_B);
		TaskSet[ii].Fn_C = FieldIdx2ImgName(TaskSet[ii].FieldIdx_C);
		TaskSet[ii].Fn_S = './Imgs/S0.png';
	}
}

// Function to get the training history (i.e. the number of presentations for each problem)
async function GetTrainHist() {

	var DataToSend = {};
	DataToSend.SubjectId = SubjectId;

	//Send data to php script
	var P1 = await fetch('./GetTrainHist.php', {
		method: 'post',
		headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' },
		body: JSON.stringify(DataToSend)
	});

	TrainHistory = await P1.json();
}

// This function is called in the Response trial object stimulus function
function GetFullQuestion() {
	var HtmlString = '<table style="text-align:center;border:1px solid white;" align="center"><tbody><tr>' +
		'<td><img src="' + CurrentQuestion.Fn_A + '" width="'+ ImgSize.toString() +'px;">' + '</td>' +
		'<td><img src="' + CurrentQuestion.Fn_B + '" width="'+ ImgSize.toString() +'px;">' + '</td>' +
		'<td><img src="' + CurrentQuestion.Fn_S + '" width="'+ ImgSize.toString() +'px;"></td>' +
		'</tr></tbody></table>';
	return HtmlString;
}

// OnMouseOver and OnMouseOut functions
function OnMouseOver(Img) {
	Img.width = ImgSize - 2;
}
function OnMouseOut(Img) {
	Img.width = ImgSize;
}

// Function to construct the ArrayOfResponseTags
function GetArrayOfResponseTags() {
	var AORT = [];
	for (var FieldIdx = 0; FieldIdx < FieldSize; FieldIdx++) {
		var ImgName = FieldIdx2ImgName(FieldIdx);
		var ResponseTag =
			'<img src="' + ImgName +
			'" width='+ ImgSize.toString() +'px;' + 
			' id="Resp_' + FieldIdx.toString().padStart(2, '0') +
			'" onclick="javascript:ImgClicked(this.id)"' +
			' onmouseover="OnMouseOver(this);"' +
			' onmouseout="OnMouseOut(this);">';
		AORT.push(ResponseTag);
	}
	return AORT;
}

// This function is called in the Response trial object stimulus function
function GetSparkOptions() {
	// Get the shuffled array and turn into string:
	var HtmlString = Shuffle(ArrayOfResponseTags).toString();
	// Add the table header:
	HtmlString = '<table style="text-align:center;"> <tbody><tr> <td colspan="3" align="center"> <table><tr><td>' + HtmlString;

	// Replace the 1st comma with a column separator (top row, between 1st and 2nd image):
	HtmlString = HtmlString.replace(',', '</td><td>');

	// Replace the 2nd comma with a column separator (top row, between 2nd and 3rd image):
	HtmlString = HtmlString.replace(',', '</td><td>');

	// Replace the 3rd comma with a column, row, and table separator (end of top row, start of 2nd):
	HtmlString = HtmlString.replace(',', '</td></tr></table><tr><td>');
	
	// Replace the 4th comma with a column separator (bottom row, between 1st and 2nd image):
	HtmlString = HtmlString.replace(',', '</td><td>');

	// Replace the 5th comma with a column separator (bottom row, between 2nd and 3rd image):
	HtmlString = HtmlString.replace(',', '</td><td>');

	// Close the table:
	HtmlString = HtmlString + '</td></tr></tbody></table>';
	return HtmlString;
}

// Give all but one of the FieldIdxs a black boarder
function BlackenBoarders(FIdxR) {
	// Identify the FieldIdxs to turn black:
	var Idxs = Array.from(Array(FieldSize).keys());
	Idxs = Idxs.filter(function (x) {
		if (x !== FIdxR) {
			return true;
		} else {
			return false;
		}
	});
	// Loop over those FieldIdxs:
	for (var i = 0; i < Idxs.length; i++) {
		var Ids2change = 'Resp_' + Idxs[i].toString().padStart(2, '0');
			document.getElementById(Ids2change).style = "border: 1px solid #000000;border-radius: 25px;" +
			"width:"+(ImgSize-2).toString()+"px;";
	}
}

// The ImagClicked function is called when an image in the response table is clicked ...
// ... This functionality is specified in the GetArrayOfResponseTags() function
async function ImgClicked(Id) {

	// If we are not accepting responses, return the function i.e., don't do anything further)
	if (!AcceptResponse) { return; }

	// Increment the attempt number:
	AttemptNum = AttemptNum + 1;

	// Specify the TrialType, FieldId, Correct, and RT variables
	var TrialType = CurrentQuestion.TrialType;
	var FieldIdx_Response = parseInt(Id.slice(-2));
	var Correct = FieldIdx_Response === CurrentQuestion.FieldIdx_C;
	var RT = jsPsych.getTotalTime() - StartTime_ResponsePrompt;

	// Do not provide feedback, or save any data, if the response came more than 2 mins after the response window started
	if (RT > (2 * 60 * 1000)) {
		window.location.reload();
		return;
	}

	// Save the data:
	var DataToSend = {};
	DataToSend.SubjectId = SubjectId;
	DataToSend.FieldSize = FieldSize;
	DataToSend.SessionId = SessionId;
	DataToSend.TrialId = TrialId;
	DataToSend.PairId = CurrentQuestion.PairId;
	DataToSend.TrialType = TrialType;
	DataToSend.OppId = CurrentQuestion.OppId;
	DataToSend.FieldIdx_A = CurrentQuestion.FieldIdx_A;
	DataToSend.FieldIdx_B = CurrentQuestion.FieldIdx_B;
	DataToSend.FieldIdx_C = CurrentQuestion.FieldIdx_C;
	DataToSend.AttemptNum = AttemptNum;
	DataToSend.FieldIdx_R = FieldIdx_Response;
	if (Correct) {
		DataToSend.Correct = 1;
	} else {
		DataToSend.Correct = 0;
	}
	DataToSend.RT = RT;

	//Send data to php script
	fetch('./WriteTaskIO.php', {
		method: 'post',
		headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' },
		body: JSON.stringify(DataToSend)
	});

	// Colour the boarders and end the trial (if we need to):
	if (TrialType === 'Sup') {
		if (!Correct) {
			document.getElementById(Id).style = "border: 1px solid #ff0000;border-radius: 25px;" +
				"width:"+(ImgSize-2).toString()+"px;";

		} else {
			// Set AcceptResponse to be false:
			AcceptResponse = false;

			// Set the current (correct) response to have a green border:
			document.getElementById(Id).style = "border: 1px solid #00ff00;border-radius: 25px;" +
				"width:"+(ImgSize-2).toString()+"px;";

			// Set all other responses to have a black border:
			BlackenBoarders(FieldIdx_Response);

			// Sleep for 4 second:
			await Sleep(4000);

			// End the trial:
			jsPsych.finishTrial();
		}
	} else {
		// Set AcceptResponse to be false:
		AcceptResponse = false;

		// Set the current (correct) response to have a green border:
		document.getElementById(Id).style = "border: 1px solid #0000ff;border-radius: 25px;" +
			"width:"+(ImgSize-2).toString()+"px;";

		// Set all other responses to have a black border:
		BlackenBoarders(FieldIdx_Response);

		// Sleep for 1 second:
		await Sleep(1000);

		// End the trial:
		jsPsych.finishTrial();
	}
}

// Called at the very end of the Promise chain to run jsPsych
function RunTrialLoop() {
	var TrialLoop = {
		//timeline: [PreTrialOps, Fixation, Show_A, ICI, Show_B, Show_S, ResponsePrompt],
		timeline: [PreTrialOps, Fixation, ResponsePrompt],
		loop_function: function () { return true; }
	};
	jsPsych.run([PreloadImgs, EnterFullscreen, TrialLoop, ExitFullscreen]);
}

// Specify the Promise chain
async function PromiseChain() {
	await GetSessionDef();

	// Set the ImgSize and get the ArrayOfResponseTags
	var ScreenHeight = window.screen.height;
	var ScreenWidth = window.screen.width;
	if (ScreenHeight > ScreenWidth) {
		ImgSize = Img2ScreenRatio * ScreenWidth;
	} else {
		ImgSize = Img2ScreenRatio * ScreenHeight;
	}
	ArrayOfResponseTags = GetArrayOfResponseTags();

	await GetTrainHist();
	await SeedRng();
	RunTrialLoop();
}