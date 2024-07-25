% Loads the GoogLeNet model and displays the layes
Net = googlenet('Weights','imagenet');
disp(Net.Layers);
InputSize = Net.Layers(1).InputSize;

%% Loop through the images and resize them to be the correct input size
ImgList = dir('Imgs\*.png');
h = waitbar(0,'Resizing...');
for ii = 1:numel(ImgList)
    ImgName = sprintf('Imgs%s%s',filesep,ImgList(ii).name);
    Img = imread(ImgName);
    Img = imresize(Img,InputSize(1:2));
    imwrite(Img,sprintf('rImgs%s%s',filesep,ImgList(ii).name));
    if mod(ii,37)==0
        waitbar(ii/numel(ImgList),h);
    end
end
close(h);

%% Get layer represenations
Imgs = dir('rImgs\*.png');
Imgs = cellfun(@(s1,s2)[s1,filesep,s2],...
    {Imgs.folder},{Imgs.name},'UniformOutput',false)';
Layers = {'pool1-norm1';'inception_4c-pool';'loss3-classifier';'prob'};
Labels = categorical(repmat({'N/A'},12,1));
X = cell(numel(Layers),1);
for ii = 1:numel(Imgs)
    ImgName = Imgs{ii};
    Img = imread(ImgName);
    
    Labels(ii) = classify(Net,Img);
    
    for iL = 1:numel(Layers)
        x = activations(Net,Img,Layers{iL},'OutputAs','columns');
        if ii == 1
            X{iL} = nan(numel(x),numel(Imgs));
        end
        X{iL}(:,ii) = x;
    end
end
SimMats = cellfun(@corr,X,'UniformOutput',false);
disp(Labels);

figure;
for iIm = 1:3
    subplot(1,3,iIm);
    imagesc(SimMats{iIm});
    axis square;
    colormap(hot);
end

%%
h = @(p) sum(p.* -log2(p),1);
H = h(X{end})';
disp(H);

% Most sure (low entropy)
[~,ii] = min(H);
Labels(ii)
winopen(Imgs{ii})

% Most unsure (high h)
[~,ii] = max(H);
Labels(ii)
winopen(Imgs{ii})

%%
rng(1);
SimStruct = struct();
nIter = 1e5;
Tri = tril(true(6),-1);
fh = waitbar(0,'Resizing...');
for iIter = 1:nIter
    s = randperm(numel(Imgs))'<=6;
    SimStruct(iIter,1).s = s;
    S = logical(s*s');
    SSimMat = reshape(SimMats{1}(S),6,6);
    %SSimMats = cellfun(@(M)reshape(M(S),6,6),SimMats,'UniformOutput',false);
    vSM = SSimMat(Tri);
    SimStruct(iIter,1).vSM = vSM;
    SimStruct(iIter,1).Mu = mean(vSM);
    SimStruct(iIter,1).Var = moment(vSM,2);
    SimStruct(iIter,1).Skw = moment(vSM,3);
    if mod(iIter,37)==0
        waitbar(iIter/nIter,fh);
    end
end

%%
for iIter = 1:nIter
    SimStruct(iIter,1).ImgNames = Imgs(SimStruct(iIter,1).s);
end

%%
SimTable = struct2table(SimStruct);
SimTable.Mu = -zscore(SimTable.Mu);
SimTable.Var = zscore(SimTable.Var);
SimTable.Skw = -zscore(abs(SimTable.Skw));
SimTable = sortrows(SimTable,'Var','descend');

%%
ii = 3;
figure;
for jj = 1:6
    subplot(2,3,jj);
    fn = SimTable.ImgNames{ii}{jj};
    II = imread(fn);
    imshow(II);
    [~,sfn] = fileparts(fn);
    disp(sfn);
end
disp('/n');


%%
Chosen = {
    '.\rImgs\b4nnA02r207-g3nnB03r231-w5nnC06r141_i1d324_A.png'
    '.\rImgs\g3nnA04r007-o3nsC03r349-y5nnB03r138_i3d331_A.png'
    '.\rImgs\b4nnC02r125-o2nsA04r098-p6nnB04r087_i1d028_A.png'
    '.\rImgs\p6nnA06r005-w4nsC01r246-y5nnB06r309_i2d036_A.png'
    '.\rImgs\g3nnC02r174-w4nsA04r310-w5nnB00r172_i3d009_A.png'
    '.\rImgs\b4nnB02r040-o3nsC00r047-w4nsA01r186_i1d354_A.png'
    };
figure;
for jj = 1:6
    subplot(2,3,jj);
    fn = Chosen{jj};
    II = imread(fn);
    imshow(II);
    [~,sfn] = fileparts(fn);
    disp(sfn);
end

%%
s = ismember(Imgs,Chosen);
S = logical(s*s');
SSimMat = reshape(SimMats{1}(S),6,6);
vSM = SSimMat(Tri);
mean(vSM)
moment(vSM,2)
moment(vSM,3)