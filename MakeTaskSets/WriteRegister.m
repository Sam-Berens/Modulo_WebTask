function [] = WriteRegister()

rng(0);
nSubjects = 50;
Register = GenRegister(nSubjects);
for iSubject = 1:nSubjects
    webwrite('http://139.184.128.239/b01/WriteRegister.php',...
        'SubjectId',Register(iSubject).SubjectId,...
        'Large2Small',Register(iSubject).Large2Small,...
        'ImgPerms',Register(iSubject).ImgPerms,...
        'TaskSets',Register(iSubject).TaskSets);
end

return