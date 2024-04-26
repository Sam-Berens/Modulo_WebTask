var ArrayOfResponseTags = [];
for (var FieldIdx = 0; FieldIdx < 7; FieldIdx++) {
    var ImgName = FieldIdx2ImgName(FieldIdx);
    var ResponseTag =
        '<img src="' + ImgName +
        '" width="150px" id="Resp_' + FieldIdx.toString().padStart(2, '0') +
        '" onclick="javascript:ImgClicked(this.id)">';
    ArrayOfResponseTags.push(ResponseTag);
}