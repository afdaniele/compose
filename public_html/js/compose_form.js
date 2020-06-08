function randomStr(len) {
    let alphabet = '0123456789abcdefghijklmnopqrstuvxywz';
    let ans = '';
    for (let i = len; i > 0; i--) {
        ans += alphabet[Math.floor(Math.random() * alphabet.length)];
    }
    return ans;
}


function human_key(key) {
    if (typeof key == 'string') {
        // remove underscores and external spaces
        key = key.replace('_', ' ').trim();
        // apply ucfirst
        return key.charAt(0).toUpperCase() + key.slice(1);
    }
    return String(key);
}


function getHTML5TypeByTypeName(name) {
    /**
     * The HTML5 types are the following:
     *  text, password, datetime, datetime-local, date, month, time, week, number, email, url, search, tel, color
     */
    switch (name) {
        case 'text':
        case 'key':
        case 'version':
        case 'alpha':
        case 'alphabetic':
        case 'alphaspace':
        case 'alphanumeric':
        case 'alphanumericspace':
            return 'text';
        case 'numeric':
        case 'float':
            return 'number';
        case 'password':
            return 'password';
        case 'email':
            return 'email';
        default:
            return null;
    }
}//getHTML5TypeByTypeName


class ComposeFormFieldInput {

    constructor(key, type, placeholder = null, disabled = false) {
        this.ID = randomStr(32);
        this.key = key;
        this.type = type;
        this.placeholder = placeholder;
        this.disabled = disabled;
    }

    toHTML(value = '') {
        return `
        <input
            name="{key}"
            type="{type}"
            class="form-control"
            placeholder="{placeholder}"
            value="{value}"
            id="{ID}"
            {attribute_html}
        >
        `.format(
            {
                "ID": this.ID,
                "key": this.key,
                "type": getHTML5TypeByTypeName(this.type),
                "placeholder": (this.placeholder == null) ? '' : this.placeholder,
                "value": value,
                "attribute_html": (this.disabled ? 'readonly' : '')
            }
        );
    }

    serialize() {
        return $('#{0}'.format(this.ID)).val();
    }

}


class ComposeFormFieldSelect {

    constructor(key, values, labels, disabled = false) {
        this.ID = randomStr(32);
        this.key = key;
        this.values = values;
        this.labels = (labels === undefined) ? values : labels;
        this.disabled = disabled;
    }

    toHTML(value = '') {
        let labels = this.labels;
        return `
        <select
            name="{key}"
            type="select"
            class="form-control"
            id="{ID}"
            {attribute_html}
        >
        {options}
        </select>
        `.format(
            {
                "ID": this.ID,
                "key": this.key,
                "attribute_html": (this.disabled ? 'readonly' : ''),
                'options': this.values.map(function (val, idx) {
                    return '<option value="{value}" {attribute_html}>{label}</option>'.format(
                        {
                            value: val,
                            attribute_html: (val === value) ? 'selected' : '',
                            label: labels[idx]
                        }
                    )
                }).join()
            }
        );
    }

    serialize() {
        return $('#{0}'.format(this.ID)).val();
    }

}


class ComposeFormFieldSwitch {

    constructor(key, disabled = false) {
        this.ID = randomStr(32);
        this.key = key;
        this.disabled = disabled;
    }

    toHTML(value = false) {
        return `
        <input type="checkbox" data-toggle="toggle" data-onstyle="primary"
            data-class="fast" data-size="small" style="margin-top:7px"
            name="{key}" id="{ID}"
            {attribute_html}
        >
        `.format(
            {
                "ID": this.ID,
                "key": this.key,
                "attribute_html": (this.disabled ? 'disabled' : '') + ' ' + (value ? 'checked' : '')
            }
        );
    }

    serialize() {
        return $('#{0}'.format(this.ID)).val();
    }

}


class ComposeSchemaAtom {

    constructor(key, schema) {
        this.ID = randomStr(32);
        this.key = key;
        this.type = schema.type;
        this.details = schema.details;
        this.values = schema.values;
        this.opts = (schema['__form__'] === undefined) ? {} : schema['__form__'];
        // use given title (if any), fall back to ucfirst key
        this.title = (this.opts['title'] === undefined) ? human_key(key) : this.opts['title'];
        // create children
        this.child = null;
        switch (this.type) {
            case "alphabetic":
            case "alphanumeric":
            case "numeric":
            case "float":
            case "password":
            case "text":
            case "email":
            case "key":
            case "version":
                this.child = new ComposeFormFieldInput(
                    key,
                    this.type,
                    this.opts['placeholder'],
                    this.opts['disabled']
                );
                break;
            case "boolean":
                this.child = new ComposeFormFieldSwitch(
                    key,
                    this.opts['disabled']
                );
                break;
            case "enum":
                this.child = new ComposeFormFieldSelect(
                    key,
                    this.values,
                    this.opts['labels'],
                    this.opts['disabled']
                );
                break;
            case "array":
                break;
            case "object":
                break;
        }
    }

    toHTML(value) {
        return `
        <br/>
        <div class="input-group">
            <span class="input-group-addon text-bold">{title}</span>
            {child}
        </div>
        <span class="help-block text-left">{details}</span>
        `.format(
            {
                "title": this.title,
                "child": (this.child === null) ? 'EMPTY' : this.child.toHTML(value),
                "details": this.details
            }
        );
    }

    serialize() {
        return this.child.serialize();
    }

    static isAtomSchema(schema) {
        return getHTML5TypeByTypeName(schema.type) != null || ['enum', 'boolean'].includes(schema.type);
    }

}


class ComposeFormGroup {

    constructor(name, details, ns = null) {
        this.ID = randomStr(32);
        this.name = name;
        this.details = details;
        this.ns = ns;
        this.children = [];
    }

    add(child) {
        this.children.push(child);
    }

    toHTML(values) {
        let content = [];
        let group_value = this.ns != null ? values[this.ns] : values;
        $.each(this.children, function (k, c) {
            let v = (group_value[k] === undefined) ? (c instanceof ComposeSchemaAtom ? '' : {}) : group_value[k];
            content.push(c.toHTML(v));
        });
        content = content.join('');
        // ---
        return (this.name === null) ? content : `
            <div id="{id}" class="compose-form-group" style="margin-top: 40px">
                <h4 style="margin-bottom: 0">{name}</h4>
                <span class="help-block text-left">{details}</span>
                <div style="margin-left: 20px; padding-left: 20px; border-left: 1px solid lightgrey; padding-bottom: 10px; margin-bottom: 30px">
                    {content}
                </div>
            </div>
        `.format({'id': 'ND', 'name': this.name, 'details': this.details, 'content': content});
    }

    serialize() {
        return {};
    }

}


class ComposeFormExtender {

    constructor(templates = {}) {
        this.ID = randomStr(32);
        this.templates = templates;
    }

    toHTML() {
        if (typeof this.templates !== 'object' || this.templates === null) {
            return '';
        }
        // create options for dropdown
        let options = [];
        $.each(this.templates, function (k, s) {
            let encoded_schema = CryptoJS.enc.Base64.stringify(CryptoJS.enc.Utf8.parse(s));
            options.push(
                `
                <li><a href="#" class="compose-form-extender-button" data-schema="{schema}">{name}</a></li>
                `.format({
                    schema: encoded_schema, name: human_key(k)
                })
            );
        });
        // no templates
        if (options.length === 0) {
            return '';
        }
        // add options to a dropdown button
        return `
        <div class="btn-group">
            <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="glyphicon glyphicon-plus" aria-hidden="true"></span> 
                &nbsp;
                Add new element 
                &nbsp;
                <span class="caret"></span>
            </button>
            <ul class="dropdown-menu">
                {options}
            </ul>
        </div>
        `.format({options: options});
    }

    serialize() {
        return null;
    }

}


class ComposeFormObject {

    constructor(name, details, templates = null, ns = '') {
        this.ID = randomStr(32);
        this.name = name;
        this.type = 'object';
        this.details = details;
        this.templates = templates;
        this.ns = ns;
        this.data = {};
    }

    add(key, child) {
        this.data[key] = child;
    }

    toHTML(values) {
        // collect children
        let group = new ComposeFormGroup(this.name, this.details);
        let group_values = [];
        // iterate over values
        $.each(this.data, function (k, s) {
            group.add(s);
            // TODO: check if k in values
            group_values.push(values[k]);
        });
        // add button for templates extension
        group.add(new ComposeFormExtender(this.templates));
        group_values.push(null);
        // ---
        return group.toHTML(group_values);
    }

    serialize() {
        let ser = {};
        $.each(this.data, function (k, s) {
            ser[k] = s.serialize();
        });
        return ser;
    }

    static from_schema(name, schema, ns = '') {
        let group = new ComposeFormObject(name, schema.details, schema._templates, ns);
        $.each(schema._data, function (k, s) {
            if (ComposeSchemaAtom.isAtomSchema(s)) {
                group.add(k, new ComposeSchemaAtom(k, s));
            } else {
                if (s.type === 'object') {
                    group.add(k, ComposeFormObject.from_schema(human_key(k), s, '{0}.{1}'.format(ns, k)));
                }
                if (s.type === 'array') {
                    group.add(k, ComposeFormArray.from_schema(human_key(k), s, '{0}[{1}]'.format(ns, k)));
                }
            }
        });
        return group;
    }

}


class ComposeFormArray {

    constructor(name, details, templates = null, ns = '') {
        this.ID = randomStr(32);
        this.name = name;
        this.type = 'array';
        this.details = details;
        this.templates = templates;
        this.ns = ns;
        this.data = [];
    }

    add(child) {
        this.data.push(child);
    }

    toHTML(values) {
        // check if the template Entity in _data needs to be broadcasted
        if (this.data.length !== 1 && values.length > 0 && this.data.length !== values.length) {
            throw new Error('Entities of type Array should have a single Entity in _data or as many as the number of given values. {0} != {1}'.format(this.data.length, values.length));
        }
        let broadcast_effect = (this.data.length > 1) + 0;
        let schemas = this.data;
        // collect children
        let group = new ComposeFormGroup(this.name, this.details);
        let group_values = [];
        // iterate over values
        $.each(values, function (k, v) {
            let s = schemas[k * broadcast_effect];
            group.add(new ComposeSchemaAtom(k, s));
            group_values.push(v);
        });
        // add button for templates extension
        group.add(new ComposeFormExtender(this.templates));
        group_values.push(null);
        // ---
        return group.toHTML(group_values);
    }

    serialize() {
        return [];
    }

    static from_schema(name, schema, ns = '') {
        let group = new ComposeFormArray(name, schema.details, schema._templates, ns);
        $.each(schema._data, function (k, s) {
            if (ComposeSchemaAtom.isAtomSchema(s)) {
                group.add(new ComposeSchemaAtom(k, s));
            } else {
                if (s.type === 'object') {
                    group.add(ComposeFormArray.from_schema(human_key(k), s, '{0}.{1}'.format(ns, k)));
                }
                if (s.type === 'array') {
                    group.add(ComposeFormArray.from_schema(human_key(k), s, '{0}[{1}]'.format(ns, k)));
                }
            }
        });
        return group;
    }

}


class ComposeForm {

    static _forms = {};

    constructor(name, schema, id=null, klass=null) {
        this.name = name;
        this.schema = schema;
        this.id = (id == null)? randomStr(32) : id;
        this.class = (klass == null)? '' : klass;
        // ---
        this.schema.type = 'object';
        this.content = ComposeFormObject.from_schema(
            this.name, this.schema, ''
        );
        // store form
        ComposeForm._forms[this.id] = this;
    }

    render(container_id, values={}) {
        $(container_id).html(this.content.toHTML(values));
    }

    serialize() {
        return this.content.serialize();
    }

    static get(id) {
        return ComposeForm._forms[id];
    }

}