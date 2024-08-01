function [Register] = GenRegister(nSubjects)

% Select the task sets
TaskSet06 = [...
    00;
    01;
    02;
    04;
    05;
    07;
    09;
    11;
    12;
    14;
    15;
    16;
    18;
    19;
    20;
    22;
    24;
    25;
    27;
    29;
    30;
    33;
    35];


SupZeroPlus = TaskSet06(...
    ismember(TaskSet06, (0:5)) | (mod(TaskSet06, 6) == 0)...
    );

%% Print figures for each training set
fh = figure('units','normalized','outerposition',[0 0 1 1]);

M06 = zeros(6);
M06(TaskSet06+1) = 1;
M06((M06~=M06')&(~M06)) = 0.5;
imagesc(M06);
colormap(copper);
axis square off;
title('Set of 6');

type = '-dpng';
print('Grid.png',type);
close(fh);


%% Pre-allocate the Register data structures
Register = repmat(struct(...
    'SubjectId','',...
    'ImgPerm','',...
    'TaskSet',''),...
    nSubjects,1);

%% Loop through each subject to make their task set
iSubject = 1;
ConditionMet = false;
while ~ConditionMet
    
    % Set the ImagePerms
    Perm = randperm(6)' -1;
    
    %% TaskSet for the set of 6
    nSup = numel(TaskSet06);
    nSupZeroPlus = numel(SupZeroPlus);
    nSupNonZeroPlus = nSup - nSupZeroPlus;
    nUns = (6^2) - nSup;
    for PairId = 0:((6^2) - 1)
        TrialObj = struct;
        TrialObj.FieldSize = 6;
        TrialObj.PairId = PairId;
        TrialObj.OppId = 0;
        
        % Calculate a, b and c
        a = mod(PairId,6);
        b = mod(floor(PairId/6),6);
        c = mod(a+b,6);
        TrialObj.FieldIdx_A = a;
        TrialObj.FieldIdx_B = b;
        TrialObj.FieldIdx_C = c;
        
        % Check whether this pair is supervised
        Sup = ismember(PairId,TaskSet06);
        
        % Label the trial types and add Ptarget
        if Sup && ~ismember(PairId,SupZeroPlus)
            TrialObj.TrialType = 'Sup';
            TrialObj.Ptarget = 3/((nSupNonZeroPlus*3)+(nSupZeroPlus*2)+(nUns*1));
        elseif Sup
            TrialObj.TrialType = 'Sup';
            TrialObj.Ptarget = 2/((nSupNonZeroPlus*3)+(nSupZeroPlus*2)+(nUns*1));
        else
            TrialObj.TrialType = 'Uns';
            TrialObj.Ptarget = 1/((nSupNonZeroPlus*3)+(nSupZeroPlus*2)+(nUns*1));
        end
        
        % Construct an array of trial objects
        if PairId == 0
            Trials = TrialObj;
        else
            Trials(PairId+1,1) = TrialObj;
        end
        
    end
    
    %% Make JSON data
    Json.ImgPerm = jsonencode(Perm);
    Json.TaskSet = jsonencode(Trials);
    
    %% Generate a SubjectId
    TextToHash = [...
        Json.ImgPerm,...
        Json.TaskSet];
    Hash = mMD5(TextToHash);
    SubjectId = Hash(end-7:end);
    
    %% Add to the Register structure
    if iSubject == 1
        Register(iSubject,1).ImgPerm = Json.ImgPerm;
        Register(iSubject,1).TaskSet = Json.TaskSet;
        Register(iSubject,1).SubjectId = SubjectId;
        iSubject = iSubject + 1;
    elseif iSubject <= nSubjects
        if ~ismember(SubjectId,{Register.SubjectId}')
            Register(iSubject,1).ImgPerm = Json.ImgPerm;
            Register(iSubject,1).TaskSet = Json.TaskSet;
            Register(iSubject,1).SubjectId = SubjectId;
            iSubject = iSubject + 1;
        end
        if iSubject == (nSubjects+1)
            ConditionMet = true;
        end
    end
    
end

return