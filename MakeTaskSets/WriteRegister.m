function [] = WriteRegister()

rng(0);
nSubjects = 100;
Register = GenRegister(nSubjects);
for iSubject = 1:nSubjects
    webwrite('http://139.184.128.239/b01/WriteRegister.php',...
         'SubjectId',Register(iSubject).SubjectId,...
         'ImgPerm',Register(iSubject).ImgPerm,...
         'TaskSet',Register(iSubject).TaskSet);
end

return