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
Tri = tril(true(12),-1);
for iIter = 1:nIter
    s = randperm(numel(Imgs))'<=12;
    SimStruct(iIter,1).s = s;
    S = logical(s*s');
    SSimMats = cellfun(@(M)reshape(M(S),12,12),SimMats,'UniformOutput',false);
    vSM = SSimMats{1}(Tri);
    SimStruct(iIter,1).vSM = vSM;
    SimStruct(iIter,1).Mu = mean(vSM);
    SimStruct(iIter,1).Var = moment(vSM,2);
    SimStruct(iIter,1).Skw = moment(vSM,3);
end