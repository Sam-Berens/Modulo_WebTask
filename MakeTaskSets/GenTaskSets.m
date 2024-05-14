function [TaskSets] = GenTaskSets(fieldSize,nI,pSup,pMir)

% Check and set the inputs
if nargin < 1
    fieldSize = 7;
end
if nargin < 2
    nI = 1e4;
end
if nargin < 4
    pSup = 0.7;
    pMir = 0.5;
    if nargin == 3
        error('If you specify pSup, you must also specify pMir;');
    end
end

%% Obtain values of p1 and p2
% ... using a non-linear solver to zero the difference between the expected
% and the target proportions (pSup and pMir).
p = fsolve(...
    @(x) ExpectedMinusTargetPs(x,pSup,pMir),...
    [0.5,0.5],...
    optimoptions('fsolve','Display','none'));
p1 = p(1);
p2 = p(2);

%% Calculate expected proportions
expected.pSup = (p1*p2*2 + p1*(1-p2)) / 2;
expected.pUns = (2*(1-p1) + p1*(1-p2)) / 2;
expected.pMir = p1*p2 + (1-p1);
expected.pNom = p1*(1-p2); %#ok<STRNU>
%disp('Expected proportions:');
%disp(expected);

%% Derived variables
numberOfCells = fieldSize^2;
numberInTril = (numberOfCells - fieldSize)/2;

%% Set the linear indices
Idx = reshape(...
    (1:(fieldSize^2))',...
    fieldSize,fieldSize);

%% Variable naming convention
% Dia : Diagonal elements;
% Low : Lower triangle elements;
% Upp : Upper triangle elements;
% Sup : Supervised elements;
% Uns : Unsupervised elements;
% Mir : Mirrored elements (mirror pairs in the same
%       supervised-vs-unsupervised set);
% Nom : Non-mirrored elements (mirror pairs are not in the same
%       supervised-vs-unsupervised set);
% ---
% NOTE: Variables specified below that adhere to this naming convention
%       will be logical matrices of size(fieldSize,fieldSize);

%% Iteration loop
TaskSets = repmat(struct('Sup',NaN,'ssd',NaN,'nUnsDia',NaN,'nUnsMir',NaN,'nUnsNom',NaN),nI,1);
h = waitbar(0,'Working...');
for ii = 1:nI
    %% Step 1a : Select the DiaSup elements
    expNumEl = p1*fieldSize;
    selNumEl = floor(expNumEl) + double((expNumEl-floor(expNumEl))>rand(1));
    DiaSup = diag(randperm(fieldSize) <= selNumEl);
    
    %% Step 1b : Select the DiaUns elements
    DiaUns = ~DiaSup & logical(eye(fieldSize));
    
    %% Step 2a : Select the LowSup elements
    expNumEl = p1*numberInTril;
    selNumEl = floor(expNumEl) + double((expNumEl-floor(expNumEl))>rand(1));
    LowSup = ismember(Idx,datasample(nonzeros(tril(Idx,-1)),selNumEl,'Replace',false));
    
    %% Step 2b : Subdivide the LowSup elements into LowSupMir elements
    expNumEl = p2*sum(LowSup,'all');
    selNumEl = floor(expNumEl) + double((expNumEl-floor(expNumEl))>rand(1));
    LowSupMir = ismember(Idx,datasample(Idx(LowSup),selNumEl,'Replace',false));
    
    %% Step 2c : Subdivide the LowSup elements into LowSupNom elements
    LowSupNom = LowSup & (~LowSupMir);
    
    %% Step 2d : Select the UppSupMir elements
    UppSupMir = LowSupMir';
    
    %% Step 2e : Select the UppUnsNom elements
    UppUnsNom = LowSupNom';
    
    %% Step 3 : Choose some sub-sample of LowSupNom to become LowUnsNom...
    % ... the corresponding elements in UppUnsNom will become UppSupNom;
    expNumEl = 0.5*sum(LowSupNom,'all');
    selNumEl = floor(expNumEl) + double((expNumEl-floor(expNumEl))>rand(1));
    LowUnsNom = ismember(Idx,datasample(Idx(LowSupNom),selNumEl,'Replace',false));
    LowSupNom(LowUnsNom) = false;
    UppUnsNom(LowUnsNom') = false;
    UppSupNom = false(fieldSize);
    UppSupNom(LowUnsNom') = true;
    
    %% Step 3a : Select the LowUnsMir
    LowUnsMir = tril(~LowSup,-1);
    
    %% Step 3b : Select the UppUnsMir
    UppUnsMir = LowUnsMir';
    
    %% Make the final Supervised and Unsupervised sets
    Sup = DiaSup | LowSupMir | LowSupNom | UppSupMir | UppSupNom;
    Uns = DiaUns | LowUnsMir | LowUnsNom | UppUnsMir | UppUnsNom; %#ok<NASGU>
    
    %% Calculate an Sum Of Squared Differences score, ssd ...
    % ... This is the sum of squared differences between the row/column
    % totals for Sup pairs and the mean row/column total across all rows
    % and columns (lower values indicate a more even spread of Sup/Uns
    % pairs).
    ssd = sum((sum(Sup,1) - mean(sum(Sup,1))).^2) + ...
        sum((sum(Sup,2) - mean(sum(Sup,2))).^2);
    
    %% Store the Supervised and Unsupervised sets
    TaskSets(ii).Sup = Sup;
    TaskSets(ii).ssd = ssd;
    TaskSets(ii).nUnsDia = sum(DiaUns,'all');
    TaskSets(ii).nUnsMir = sum(LowUnsMir|UppUnsMir,'all');
    TaskSets(ii).nUnsNom = sum(LowUnsNom|UppUnsNom,'all');
    
    %% Update the waitbar
    if mod(ii,177) == 0
        waitbar(ii/nI,h);
    end
end
close(h);
return

function [p] = ExpectedMinusTargetPs(x,pSup,pMir)
p1 = x(1);
p2 = x(2);
p(1) = ((2*p1*p2 + p1*(1-p2)) / 2) - pSup;
p(2) = p1*p2 + (1-p1) - pMir;
return