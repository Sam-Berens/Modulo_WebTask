// Import the main jsPsych object
var jsPsych = initJsPsych();

// Preload jsPsych object
var PreloadImgs = {
    type: jsPsychPreload,
    images: [
        './TestShapes/i00.jpg',
        './TestShapes/i01.jpg',
        './TestShapes/i02.jpg',
        './TestShapes/i03.jpg',
        './TestShapes/i04.jpg',
        './TestShapes/i05.jpg',
        './TestShapes/i06.jpg',
        './Imgs/S0.png',
        './Imgs/S0.png'
    ]
};

// Specifiy the EnterFullscreen event
var EnterFullscreen = {
    type: jsPsychFullscreen,
    message: '<p style="color:white;">The task is ready to begin.</p><p style="color:white;">Please click the button below to continue.</p>',
    fullscreen_mode: true,
    delay_after: 1000,
    on_finish: function () { EnforceUnfocus = true; }
};

// Specifiy the ExitFullscreen event
var ExitFullscreen = {
    type: jsPsychFullscreen,
    fullscreen_mode: false,
    on_finish: function () { EnforceUnfocus = false; }
};

// Specify the pre-trial ops
var PreTrialOps = {
    type: jsPsychCallFunction,
    func: function () {
        // Update global variables
        TrialId = TrialId + 1;
        AttemptNum = -1;
        AcceptResponse = true;

        // Based on the training history, calculate Qdist (the distribution to sample from)
        var TrialCount = TrainHistory.reduce((x,y) => {return x+y;},0);
        var Pactual = TrainHistory.map((x) => {return x/TrialCount;});
        var aP = Ptarget.map((p,ii) => {return Math.max(p-Pactual[ii],0);});
        var Qdist = Ptarget.map((p,ii) => {return p+aP[ii];});
        var Qsum = Qdist.reduce((x,y) => {return x+y;},0);
        Qdist = Qdist.map((q) => {return q/Qsum;});

        // Use inverse transform sampling to select a new trial from the TimelineVars array
        var Qcum = Qdist.map((sum => value => sum += value)(0));
        var r = Rng();
        var SelectedId = Qcum.map((q)=>{return r<q}).indexOf(true);

        // Update  global variables
        CurrentQuestion = TimelineVars[SelectedId];
        TrainHistory[SelectedId] = TrainHistory[SelectedId] + 1;
    }
};

// Specifiy the Fixation event
var Fixation = {
    type: jsPsychHtmlButtonResponse,
    stimulus: '<p><font color="#ffffff" size="30px">+</font></p>',
    choices: [],
    prompt: "",
    trial_duration: 1000
};

// Trial object that presents the trial symbol
var Show_S = {
    type: jsPsychHtmlButtonResponse,
    stimulus: function () {
        var Fn = CurrentQuestion.Fn_S;
        var Tag = '<img src="' + Fn + '" width="200px"';
        return Tag;
    },
    choices: [],
    prompt: [],
    trial_duration: 1000
};

// Trial object that presents the A cue
var Show_A = {
    type: jsPsychHtmlButtonResponse,
    stimulus: function () {
        var FieldIdx = CurrentQuestion.FieldIdx_A;
        var Fn = CurrentQuestion.Fn_A;
        var Tag = '<img src="' + Fn + '" width="400px" id="Cue_' + FieldIdx.toString().padStart(2, '0') + '"';
        return Tag;
    },
    choices: [],
    prompt: [],
    trial_duration: 1000
};

// Specifiy the Inter-cue-iterval event
var ICI = {
    type: jsPsychHtmlButtonResponse,
    stimulus: '<p><font color="#ffffff" size="30px">+</font></p>',
    choices: [],
    prompt: "",
    trial_duration: 200
};

// Trial object that presents the B cue
var Show_B = {
    type: jsPsychHtmlButtonResponse,
    stimulus: function () {
        var FieldIdx = CurrentQuestion.FieldIdx_B;
        var Fn = CurrentQuestion.Fn_B;
        var Tag = '<img src="' + Fn + '" width="400px" id="Cue_' + FieldIdx.toString().padStart(2, '0') + '"';
        return Tag;
    },
    choices: [],
    prompt: [],
    trial_duration: 1000
};

// Specify the response prompt object
var ResponsePrompt = {
    type: jsPsychHtmlButtonResponse,
    stimulus: function () {
        var T0 = GetFullQuestion();
        var T1 = GetSparkOptions();
        var T0T1 = T0 + '<br /><br />' + T1;
        return T0T1;
    },
    choices: [],
    prompt: [],
    trial_duration: null,
    on_start: function () {
        StartTime_ResponsePrompt = jsPsych.getTotalTime();
    }
};