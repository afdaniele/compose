String.prototype.lstrip = function(char) {
    let regex = new RegExp('^[{0}]*'.format(char));
    return this.replace(regex, '');
};

String.prototype.rstrip = function(char) {
    let regex = new RegExp('[{0}]*$'.format(char));
    return this.replace(regex, '');
};

String.prototype.strip = function(char) {
    return this.lstrip(char).rstrip(char);
};