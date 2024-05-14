function [Register] = GenRegister(nSubjects)

% Select the task sets
[TaskSets05,TaskSets07] = SelectTaskSets(nSubjects);

%% Shuffle the task sets
TaskSets05 = TaskSets05(randperm(nSubjects));
TaskSets07 = TaskSets07(randperm(nSubjects));

%% Pre-allocate the Register data structures
Register = repmat(struct(...
    'SubjectId','','Large2Small',NaN,'ImgPerms','','TaskSets',''),...
    numel(TaskSets05),1);
Large2Small = mod((0:(nSubjects-1))',2);
ImgPerms = repmat(struct('S05','','S07',''),...
    numel(TaskSets05),1);
TaskSets = repmat(struct('S05','','S07',''),...
    numel(TaskSets05),1);

%% Loop through each subject to make their task set
if exist('Grids','dir') == 7
    rmdir('Grids\','s');
    pause(2);
end
mkdir('Grids\');
for iSubject = 1:nSubjects
    
    % Set the ImagePerms
    Perm = randperm(12)' -1;
    ImgPerms(iSubject,1).S05 = Perm(1:5);
    ImgPerms(iSubject,1).S07 = Perm(6:end);
    
    %% TaskSet for the set of 5
    nSup = numel(TaskSets05{iSubject});
    nUns = (5^2) - nSup;
    for PairId = 0:((5^2) - 1)
        TrialObj = struct;
        TrialObj.FieldSize = 5;
        TrialObj.PairId = PairId;
        TrialObj.OppId = 0;
        
        % Calculate a, b and c
        a = mod(PairId,5);
        b = mod(floor(PairId/5),5);
        c = mod(a*b,5);
        TrialObj.FieldIdx_A = a;
        TrialObj.FieldIdx_B = b;
        TrialObj.FieldIdx_C = c;
        
        % Check whether this pair is supervised
        Sup = ismember(PairId,TaskSets05{iSubject});
        
        % Label the trial types and add Ptarget
        if Sup
            TrialObj.TrialType = 'Sup';
            TrialObj.Ptarget = 3/((nSup*3)+nUns);
        else
            TrialObj.TrialType = 'Uns';
            TrialObj.Ptarget = 1/((nSup*3)+nUns);
        end
        
        % Construct an array of trial objects
        if PairId == 0
            Trials = TrialObj;
        else
            Trials(PairId+1,1) = TrialObj;
        end
        
    end
    
    % Add the trial array to the list of full task sets
    TaskSets(iSubject,1).S05 = Trials;
    
    %% TaskSet for the set of 7
    nSup = numel(TaskSets07{iSubject});
    nUns = (7^2) - nSup;
    for PairId = 0:((7^2) - 1)
        TrialObj = struct;
        TrialObj.FieldSize = 7;
        TrialObj.PairId = PairId;
        TrialObj.OppId = 0;
        
        % Calculate a, b and c
        a = mod(PairId,7);
        b = mod(floor(PairId/7),7);
        c = mod(a*b,7);
        TrialObj.FieldIdx_A = a;
        TrialObj.FieldIdx_B = b;
        TrialObj.FieldIdx_C = c;
        
        % Check whether this pair is supervised
        Sup = ismember(PairId,TaskSets07{iSubject});
        
        % Label the trial types and add Ptarget
        if Sup
            TrialObj.TrialType = 'Sup';
            TrialObj.Ptarget = 3/((nSup*3)+nUns);
        else
            TrialObj.TrialType = 'Uns';
            TrialObj.Ptarget = 1/((nSup*3)+nUns);
        end
        
        % Construct an array of trial objects
        if PairId == 0
            Trials = TrialObj;
        else
            Trials(PairId+1,1) = TrialObj;
        end
        
    end
    
    % Add the trial array to the list of full task sets
    TaskSets(iSubject,1).S07 = Trials;
    
    %% Add to the Register structure
    Register(iSubject,1).Large2Small = Large2Small(iSubject);
    Register(iSubject,1).ImgPerms = jsonencode(ImgPerms(iSubject,1));
    Register(iSubject,1).TaskSets = jsonencode(TaskSets(iSubject,1));
    
    %% Generate a SubjectId
    TextToHash = [...
        num2str(Register(iSubject,1).Large2Small),...
        Register(iSubject,1).ImgPerms,...
        Register(iSubject,1).TaskSets];
    Hash = mMD5(TextToHash);
    Register(iSubject,1).SubjectId = Hash(end-7:end);
    
    %% Print figures for each training set
    fh = figure('units','normalized','outerposition',[0 0 1 1]);
    
    M05 = zeros(5);
    M05(TaskSets05{iSubject}+1) = 1;
    M05((M05~=M05')&(~M05)) = 0.5;
    subplot(1,2,1);
    imagesc(M05);
    colormap(copper);
    axis square off;
    title('Set of 5');
    
    M07 = zeros(7);
    M07(TaskSets07{iSubject}+1) = 1;
    M07((M07~=M07')&(~M07)) = 0.5;
    subplot(1,2,2);
    imagesc(M07);
    colormap(copper);
    axis square off;
    title('Set of 7');
    
    sgtitle(Register(iSubject,1).SubjectId);
    print(sprintf('Grids%s%s.png',...
        filesep,Register(iSubject,1).SubjectId),'-dpng');
    close(fh);
     
end