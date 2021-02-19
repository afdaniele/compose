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
        key = key.replace(/_/g, ' ').trim();
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
        case 'color':
            return 'color';
        default:
            return null;
    }
}//getHTML5TypeByTypeName


class ComposeFormFieldInput {

    constructor(key, type, static_value = null, placeholder = null, disabled = false) {
        this.ID = randomStr(32);
        this.key = key;
        this.type = type;
        this.static_value = (static_value == null || false)? null : static_value;
        this.placeholder = placeholder;
        this.disabled = disabled;
        this.labelWidth = (this.type === 'color')? '90%' : '1%';
    }

    toHTML(value = '') {
        return `
        <input
            name="{key}"
            type="{type}"
            class="compose-smart-form-input form-control"
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
                "value": (this.static_value == null)? value : this.static_value,
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
        this.values = (values === undefined) ? [] : values;
        this.labels = (labels === undefined) ? values : labels;
        this.disabled = disabled;
        this.labelWidth = '60%';
    }

    toHTML(value = '') {
        let labels = this.labels;
        return `
        <select
            name="{key}"
            type="select"
            class="compose-smart-form-input form-control"
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

    YES_VALUES = ['1', 'yes', 'yep', 'yup', 'si', true, 'true'];

    constructor(key, disabled = false) {
        this.ID = randomStr(32);
        this.key = key;
        this.disabled = disabled;
        this.labelWidth = '100%';
    }

    toHTML(value = false) {
        value = this.YES_VALUES.includes(value);
        return `
        <input 
            class="compose-smart-form-input"
            type="checkbox"
            data-toggle="toggle"
            data-onstyle="primary"
            data-offstyle="warning"
            data-class="fast"
            data-size="normal"
            name="{key}"
            id="{ID}"
            {attribute_html}>
        `.format(
            {
                "ID": this.ID,
                "key": this.key,
                "attribute_html": (this.disabled ? 'disabled' : '') + ' ' + (value ? 'checked' : '')
            }
        );
    }

    serialize() {
        return $('#{0}'.format(this.ID)).prop("checked");
    }

}


class ComposeSchemaAtom {

    constructor(key, schema, opts = {}) {
        this.ID = randomStr(32);
        this.key = key;
        this.schema = schema;
        this.type = schema.type;
        this.details = schema.details;
        this.default = schema.default;
        this.values = schema.values;
        this.opts = (schema['__form__'] === undefined) ? {} : schema['__form__'];
        this.opts = {
            ...this.opts,
            ...opts
        }
        this.hidden = this.opts.hidden === true;
        this.static_value = this.opts.value;
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
            case "color":
            case "version":
            case undefined:
            case null:
                this.child = new ComposeFormFieldInput(
                    key,
                    this.type,
                    this.static_value,
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
        // register component against the ComposeForm dictionary
        ComposeForm.whiteboard[this.ID] = this;
    }

    toHTML(value) {
        return `
        <div class="compose-form-atom" style="{style}">
            <div class="input-group">
                <span class="input-group-addon text-bold" style="width: {label_width}">
                    {title}
                </span>
                {child}
                <span class="input-group-addon closure-block"></span>
            </div>
            <span class="help-block-details">
                <i class="fa fa-info-circle" aria-hidden="true"></i>&nbsp; {details}
            </span>
            <span class="help-block-default">
                default: &nbsp;<span class="help-block-default">{default}</span>
            </span>
        </div>
        `.format(
            {
                "title": this.title,
                "default": (this.default === null || this.default === undefined || this.default === '')? "null" : this.default.toString(),
                "child": (this.child === null) ? 'EMPTY' : this.child.toHTML(value),
                "details": this.details,
                "style": (this.hidden ? 'display: none' : ''),
                "label_width": this.child.labelWidth
            }
        );
    }

    serialize() {
        return this.child.serialize();
    }

    copy() {
        return new ComposeSchemaAtom(this.key, this.schema);
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
        this.children = {};
    }

    add(key, child) {
        this.children[key] = child;
    }

    toHTML(values) {
        let content = [];
        let group_value = (this.ns != null) ? values[this.ns] : values;
        $.each(this.children, function (k, c) {
            let v = (group_value[k] === undefined) ? (c instanceof ComposeSchemaAtom ? '' : {}) : group_value[k];
            content.push(c.toHTML(v));
        });
        content = content.join('');
        // ---
        return (this.name === null) ? content : `
            <div id="{id}" class="compose-form-group">
                <h4>{name}</h4>
                <h5>{details}</h5>
                <div class="compose-form-group-content">
                    {content}
                </div>
            </div>
        `.format({'id': this.ID, 'name': this.name, 'details': this.details, 'content': content});
    }

    serialize() {
        return {};
    }

}


class ComposeFormExtender {

    constructor(target, templates = {}, ns = '') {
        this.ID = randomStr(32);
        this.target = target;
        this.templates = templates;
        this.ns = ns;
    }

    toHTML() {
        // create options for dropdown
        let options = [];
        let target = this.target;
        $.each(this.templates, function (k, template) {
            options.push(
                `
                <li>
                    <a href="#" class="compose-form-extender-button" data-target="{target}" data-template="{template}">
                        {name}
                    </a>
                </li>
                `.format({
                    name: human_key(k),
                    target: target,
                    template: template.ID
                })
            );
        });
        // no templates
        if (options.length === 0) {
            return '';
        }
        // add options to a dropdown button
        return `
        <div id="{id}" class="btn-group" style="margin-bottom: 20px">
            <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <span class="glyphicon glyphicon-plus" aria-hidden="true"></span> 
                &nbsp; Add new &nbsp;
                <span class="caret"></span>
            </button>
            <ul class="dropdown-menu">
                {options}
            </ul>
        </div>
        
        <script type="application/javascript">
        $("#{id} a.compose-form-extender-button").on('click', function(){
            // get form parent 'target' and new element 'template'
            let target_id = $(this).data('target');
            let target = ComposeForm.whiteboard[target_id];
            let template_id = $(this).data('template');
            let template = ComposeForm.whiteboard[template_id];
            // instantiate a new form component from the template
            let child = template.copy();
            child.name = "*";
            child.add('__template__', new ComposeSchemaAtom(
                '__template__', 
                {type: 'text'}, 
                {hidden: true}
            ));
            // add new child to the target parent
            target.add(child);
            // compile template into HTML and add it to the parent
            let html = child.toHTML({});
            $("#{id}").prepend(html);
        });
        </script>
        `.format({id: this.ID, options: options});
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
        // register component against the ComposeForm dictionary
        ComposeForm.whiteboard[this.ID] = this;
    }

    add(key, child) {
        this.data[key] = child;
    }

    toHTML(values) {
        // collect children
        let group = new ComposeFormGroup(this.name, this.details);
        // iterate over values
        $.each(this.data, function (k, s) {
            group.add(k, s);
        });
        // ---
        return group.toHTML(values);
    }

    serialize() {
        let ser = {};
        $.each(this.data, function (k, s) {
            ser[k] = s.serialize();
        });
        return ser;
    }

    copy() {
        let copy = new ComposeFormObject(this.name, this.details, this.templates, this.ns);
        $.each(this.data, function (k, s) {
            copy.add(k, s.copy());
        })
        return copy;
    }

    static from_schema(name, schema, ns = '') {
        let opts = (schema['__form__'] === undefined) ? {} : schema['__form__'];
        name = (opts['title'] === undefined) ? name : opts['title'];
        // ---
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
        this.templates = (templates == null)? {} : templates;
        this.ns = ns;
        this.data = [];
        // ---
        // parse templates
        let host = this;
        $.each(templates, function (k, s) {
            if (ComposeSchemaAtom.isAtomSchema(s)) {
                host.templates[k] = new ComposeSchemaAtom(k, s);
            } else {
                if (s.type === 'object') {
                    host.templates[k] = ComposeFormObject.from_schema(human_key(k), s, '{0}.{1}'.format(ns, k));
                }
                if (s.type === 'array') {
                    host.templates[k] = ComposeFormArray.from_schema(human_key(k), s, '{0}[{1}]'.format(ns, k));
                }
            }
        });
        // register component against the ComposeForm dictionary
        ComposeForm.whiteboard[this.ID] = this;
    }

    add(child) {
        this.data.push(child);
    }

    toHTML(values) {
        // collect children
        let group = new ComposeFormGroup(this.name, this.details);
        let host = this;
        // iterate over values
        $.each(values, function (k, value) {
            // make sure the entry explicitly declares its template
            if (!value.hasOwnProperty('__template__')) {
                console.log("WARNING: Missing property `__template__` in an element of ComposeFormArray.")
                return;
            }
            // make sure we recognize that template
            let template_name = value['__template__'];
            if (!host.templates.hasOwnProperty(template_name)) {
                console.log("WARNING: Unknown template `{0}` in an element of ComposeFormArray.".format(template_name))
                return;
            }
            // instantiate a child from the template
            let template = host.templates[template_name];
            let child = template.copy();
            child.name = k;
            // add hidden template field
            child.add('__template__', new ComposeSchemaAtom(
                '__template__',
                {type: 'text'},
                {hidden: true}
            ));
            // add child to this object and the HTML group
            host.add(child);
            group.add(k, child);
        });
        // add button for templates extension
        group.add('__extender__', new ComposeFormExtender(this.ID, this.templates));
        // ---
        return group.toHTML(values);
    }

    serialize() {
        let ser = [];
        $.each(this.data, function (_, s) {
            ser.push(s.serialize());
        });
        return ser;
    }

    copy() {
        let copy = new ComposeFormArray(this.name, this.details, this.templates, this.ns);
        $.each(this.data, function (_, s) {
            copy.add(s.copy());
        })
        return copy;
    }

    static from_schema(name, schema, ns = '') {
        return new ComposeFormArray(name, schema.details, schema._templates, ns);
    }

}


class ComposeForm {

    static _forms = {};
    static whiteboard = {};

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

    render(container_id = null, values={}) {
        container_id = (container_id == null)? this.id : container_id;
        $(container_id).html(this.content.toHTML(values));
    }

    serialize() {
        return this.content.serialize();
    }

    static get(id) {
        return ComposeForm._forms[id];
    }

}