function [TaskSets05,TaskSets07] = SelectTaskSets(nSetsToSelect)

nIter = 1e5;

%% Generate the set of 5
TaskSets = GenTaskSets(5,nIter,0.60,0.5);

% Select sets with 1 unsupervised diagonal element ...
% ... 4 unsupervised mirrored elements ...
% ... and 4 unsupervised non-mirrored.
S = [TaskSets.nUnsDia]'==1 & ...
    [TaskSets.nUnsMir]'==4  & ...
    [TaskSets.nUnsNom]'==4;
TaskSets = TaskSets(S);

% Loop through sets to calculate the number of connected components (nCC)
% and select sets where nCC >= 8.
for iTaskSets = 1:numel(TaskSets)
    M = ones(5);
    M(TaskSets(iTaskSets,1).Sup) = 0;
    CC = bwconncomp(M,4);
    TaskSets(iTaskSets,1).nCC = CC.NumObjects;
end
S = [TaskSets.nCC]'>=8;
TaskSets = TaskSets(S);

% Sort the sets by the sum of squared differences (between row/column
% counts and mean counts) and choose the sets with the lowest scores.
[~,idx] = sort([TaskSets.ssd]'); %#ok<TRSRT>
idx = idx(1:nSetsToSelect);
Sup = {TaskSets(idx).Sup}';

% Convert the n-by-n matrices into lists of supervised zero-ordered indices
% in TaskSets05.
TaskSets05 = cellfun(@(S)find(S)-1,Sup,'UniformOutput',false);

%% Generate the set of 7
TaskSets = GenTaskSets(7,nIter,0.60,0.5);

% Select sets with 2 unsupervised diagonal element ...
% ... 6 unsupervised mirrored elements ...
% ... and 10 unsupervised non-mirrored.
S = [TaskSets.nUnsDia]'==2 & ...
    [TaskSets.nUnsMir]'==6  & ...
    [TaskSets.nUnsNom]'==10;
TaskSets = TaskSets(S);

% Loop through sets to calculate the number of connected components (nCC)
% and select sets where nCC >= 11.
for iTaskSets = 1:numel(TaskSets)
    M = ones(7);
    M(TaskSets(iTaskSets,1).Sup) = 0;
    CC = bwconncomp(M,4);
    TaskSets(iTaskSets,1).nCC = CC.NumObjects;
end
S = [TaskSets.nCC]'>=11;
TaskSets = TaskSets(S);

% Sort the sets by the sum of squared differences (between row/column
% counts and mean counts) and choose the sets with the lowest scores.
[~,idx] = sort([TaskSets.ssd]'); %#ok<TRSRT>
idx = idx(1:nSetsToSelect);
Sup = {TaskSets(idx).Sup}';

% Convert the n-by-n matrices into lists of supervised zero-ordered indices
% in TaskSets07.
TaskSets07 = cellfun(@(S)find(S)-1,Sup,'UniformOutput',false);

return