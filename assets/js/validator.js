/*
    Validator v3.3.1
    (c) Yair Even Or
    https://github.com/yairEO/validator
*/

;(function(root, factory){
    if( typeof define === 'function' && define.amd )
        define([], factory);
    else if( typeof exports === 'object' )
        module.exports = factory();
    else
        root.FormValidator = factory();
}(this, function(){
    function FormValidator( settings, formElm ){
        this.data = {}; // holds the form items' data

        this.DOM = {
            scope : formElm
        };

        this.settings = this.extend({}, this.defaults, settings || {});
        this.texts = this.extend({}, this.texts, settings.texts || {});

        this.settings.events && this.events();
    }

    FormValidator.prototype = {
        // Validation error texts
        texts : {
            invalid         : 'formato inválido',
            short           : 'input is too short',
            long            : 'input is too long',
            checked         : 'must be checked',
            empty           : '*',
            select          : 'Please select an option',
            number_min      : 'too low',
            number_max      : 'too high',
            url             : 'invalid URL',
            number          : 'not a number',
            email           : 'email address is invalid',
            email_repeat    : 'emails do not match',
            date            : 'invalid date',
            time            : 'invalid time',
            password_repeat : 'passwords do not match',
            no_match        : 'no match',
            complete        : 'input is not complete'
        },

        // default settings
        defaults : {
            alerts : true,
            events : false,
            regex : {
                url          : /^(https?:\/\/)?([\w\d\-_]+\.+[A-Za-z]{2,})+\/?/,
                phone        : /^\+?([0-9]|[-|' '])+$/i,
                numeric      : /^[0-9]+$/i,
                alphanumeric : /^[a-zA-Z0-9]+$/i,
                date: /^(?:(?:31(\/|-|\.)(?:0?[13578]|1[02]))\1|(?:(?:29|30)(\/|-|\.)(?:0?[1,3-9]|1[0-2])\2))(?:(?:1[6-9]|[2-9]\d)?\d{2})$|^(?:29(\/|-|\.)0?2\3(?:(?:(?:1[6-9]|[2-9]\d)?(?:0[48]|[2468][048]|[13579][26])|(?:(?:16|[2468][048]|[3579][26])00))))$|^(?:0?[1-9]|1\d|2[0-8])(\/|-|\.)(?:(?:0?[1-9])|(?:1[0-2]))\4(?:(?:1[6-9]|[2-9]\d)?\d{2})$/i, //dd/MM/YYYY
                //date: /^(0[1-9]|1[012])[- \/.](0[1-9]|[12][0-9]|3[01])[- \/.](19|20)\d\d/i, //MM/dd/AAAA
                email        : {
                    illegalChars : /[\(\)\<\>\,\;\:\\\/\"\[\]]/,
                    filter       : /^.+@.+\..{2,6}$/ // exmaple email "steve@s-i.photo"
                }
            },
            classes : {
                item  : 'item',
                alert : 'alert',
                bad   : 'bad'
            }
        },

        // Tests (per type)
        // each test return "true" when passes and a string of error text otherwise
        tests : {
            sameAsPlaceholder : function( item, data ){
                if( item.getAttribute('placeholder') )
                    return data.value != item.getAttribute('placeholder') || this.texts.empty;
                else
                    return true;
            },

            hasValue : function( value ){
                return value ? true : this.texts.empty;
            },

            // 'linked' is a special test case for inputs which their values should be equal to each other (ex. confirm email or retype password)
            linked : function(a, b, type){
                if( b != a ){
                    // choose a specific message or a general one
                    return this.texts[type + '_repeat'] || this.texts.no_match;
                }
                return true;
            },

            email : function(item, data){
                if ( !this.settings.regex.email.filter.test( data.value ) || data.value.match( this.settings.regex.email.illegalChars ) ){
                    return this.texts.email;
                }

                return true;
            },

            // a "skip" will skip some of the tests (needed for keydown validation)
            text : function(item, data){
                var that = this;
                // make sure there are at least X number of words, each at least 2 chars long.
                // for example 'john F kenedy' should be at least 2 words and will pass validation
                if( data.validateWords ){
                    var words = data.value.split(' ');
                    // iterate on all the words
                    var wordsLength = function(len){
                        for( var w = words.length; w--; )
                            if( words[w].length < len )
                                return that.texts.short;
                        return true;
                    };

                    if( words.length < data.validateWords || !wordsLength(2) )
                        return this.texts.complete;

                    return true;
                }

                if( data.lengthRange && data.value.length < data.lengthRange[0] ){
                    return this.texts.short;
                }

                // check if there is max length & item length is greater than the allowed
                if( data.lengthRange && data.lengthRange[1] && data.value.length > data.lengthRange[1] ){
                    return this.texts.long;
                }

                // check if the item's value should obey any length limits, and if so, make sure the length of the value is as specified
                if( data.lengthLimit && data.lengthLimit.length ){
                    while( data.lengthLimit.length ){
                        if( data.lengthLimit.pop() == data.value.length ){
                            return true;
                        }
                    }

                    return this.texts.complete;
                }

                if( data.pattern ){
                    var regex, jsRegex;

                    switch( data.pattern ){
                        case 'alphanumeric' :
                            regex = this.settings.regex.alphanumeric
                            break;
                        case 'numeric' :
                            regex = this.settings.regex.numeric
                            break;
                        case 'phone' :
                            regex = this.settings.regex.phone
                            break;
                        case 'date' :
                            regex = this.settings.regex.date
                            break;
                        default :
                            regex = data.pattern;
                    }
                    try{
                        jsRegex = new RegExp(regex).test(data.value);
                        if( data.value && !jsRegex ){
                            return this.texts.invalid;
                        }
                    }
                    catch(err){
                        console.warn(err, item, 'regex is invalid');
                        return this.texts.invalid;
                    }
                }

                return true;
            },

            number : function( item, data ){
                var a = data.value;
                // if not not a number
                if( isNaN(parseFloat(a)) && !isFinite(a) ){
                    return this.texts.number;
                }
                // not enough numbers
                else if( data.lengthRange && a.length < data.lengthRange[0] ){
                    return this.texts.short;
                }
                // check if there is max length & item length is greater than the allowed
                else if( data.lengthRange && data.lengthRange[1] && a.length > data.lengthRange[1] ){
                    return this.texts.long;
                }
                else if( data.minmax[0] && (a|0) < data.minmax[0] ){
                    return this.texts.number_min;
                }
                else if( data.minmax[1] && (a|0) > data.minmax[1] ){
                    return this.texts.number_max;
                }

                return true;
            },

            // Date is validated in European format (day,month,year)
            date : function( item, data ){
                var day, A = data.value.split(/[-./]/g), i;
                // if there is native HTML5 support:
                if( item.valueAsNumber )
                    return true;

                for( i = A.length; i--; ){
                    if( isNaN(parseFloat( data.value )) && !isFinite(data.value) )
                        return this.texts.date;
                }
                try{
                    day = new Date(A[2], A[1]-1, A[0]);
                    if( day.getMonth()+1 == A[1] && day.getDate() == A[0] )
                        return true;
                    return this.texts.date;
                }
                catch(er){
                    return this.texts.date;
                }
            },

            time : function( item, data ){
                var pattern = /^([0-1][0-9]|2[0-3]):[0-5][0-9]$/;
                if( pattern.test(data.value) )
                    return true;
                else
                    return this.texts.time;
            },

            url : function( item, data ){
                // minimalistic URL validation
                if( !this.settings.regex.url.test(data.value) )
                    return this.texts.url;

                return true;
            },

            hidden : function( item, data ){
                if( data.lengthRange && data.value.length < data.lengthRange[0] )
                    return this.texts.short;

                if( data.pattern ){
                    if( data.pattern == 'alphanumeric' && !this.settings.regex.alphanumeric.test(data.value) )
                        return this.texts.invalid;
                }

                return true;
            },

            select : function( item, data ){
                return data.value ? true : this.texts.select;
            },

            checkbox : function( item, data ){
                if( item.checked ) return true;

                return this.texts.checked;
            }
        },

        /**
         * bind events on form elements
         * @param  {Array/String} types   [description]
         * @param  {Object} formElm       [optional - form element, if one is not already defined on the instance]
         * @return {[type]}               [description]
         */
        events : function( types, formElm ){
            var that = this;

            types   = types   || this.settings.events;
            formElm = formElm || this.DOM.scope;

            if( !formElm || !types ) return;

            if( types instanceof Array )
                types.forEach(bindEventByType);
            else if( typeof types == 'string' )
                bindEventByType(types)

            function bindEventByType( type ){
                formElm.addEventListener(type, function(e){
                    that.checkitem(e.target)
                }, true);
            }
        },

        /**
         * Marks an item as invalid
         * @param  {DOM Object} item
         * @param  {String} text
         * @return {jQuery Object} - The message element for the item
         */
        mark : function( item, text ){
            if( !text || !item )
                return false;

            var that = this;

            // check if not already marked as 'bad' and add the 'alert' object.
            // if already is marked as 'bad', then make sure the text is set again because i might change depending on validation
            var item = this.closest(item, '.' + this.settings.classes.item),
                alert = item.querySelector('.'+this.settings.classes.alert),
                warning;

            if( this.settings.alerts ){
                if( alert )
                    alert.innerHTML = text;
                else{
                    warning = '<div class="'+ this.settings.classes.alert +'">' + text + '</div>';
                    item.insertAdjacentHTML('beforeend', warning);
                }
            }

            item.classList.remove(this.settings.classes.bad);

            // a delay so the "alert" could be transitioned via CSS
            setTimeout(function(){
                item.classList.add( that.settings.classes.bad );
            });

            return warning;
        },

        /* un-marks invalid items
        */
        unmark : function( item ){
            var warning;

            if( !item ){
                console.warn('no "item" argument, null or DOM object not found');
                return false;
            }

            var itemWrap = this.closest(item, '.' + this.settings.classes.item);

            if( itemWrap ){
                warning = itemWrap.querySelector('.'+ this.settings.classes.alert);
                itemWrap.classList.remove(this.settings.classes.bad);
            }

            if( warning )
                warning.parentNode.removeChild(warning);
        },

        /**
         * removes unmarks all items
         * @return {[type]} [description]
         */
        reset : function( formElm ){
            var itemsToCheck,
                that = this;

            formElm = formElm || this.DOM.scope;
            itemsToCheck = this.filterFormElements( formElm.elements );

            itemsToCheck.forEach(function(elm){
                that.unmark(elm);
            });
        },

        /**
         * Normalize types if needed & return the results of the test (per item)
         * @param  {String} type  [form item type]
         * @param  {*}      value
         * @return {Boolean} - validation test result
         */
        testByType : function( item, data ){
            data = this.extend({}, data); // clone the data

            var type = data.type;

            if( type == 'tel' )
                data.pattern = data.pattern || 'phone';

            if( !type || type == 'password' || type == 'tel' || type == 'search' || type == 'file' )
                type = 'text';

            return this.tests[type] ? this.tests[type].call(this, item, data) : true;
        },

        prepareitemData : function( item ){
            var nodeName = item.nodeName.toLowerCase(),
                id = Math.random().toString(36).substr(2,9);

            item["_validatorId"] = id;
            this.data[id] = {};

            this.data[id].value   = item.value.replace(/^\s+|\s+$/g, "");  // cache the value of the item and trim it
            this.data[id].valid   = true;                                  // initialize validity of item
            this.data[id].type    = item.getAttribute('type');             // every item starts as 'valid=true' until proven otherwise
            this.data[id].pattern = item.getAttribute('pattern');

            // Special treatment
            if( nodeName === "select" )
                this.data[id].type = "select";

            else if( nodeName === "textarea" )
                this.data[id].type = "text";

            /* Gather Custom data attributes for specific validation:
            */
            this.data[id].validateWords  = item.getAttribute('data-validate-words')        || 0;
            this.data[id].lengthRange    = item.getAttribute('data-validate-length-range') ? (item.getAttribute('data-validate-length-range')+'').split(',') : [1];
            this.data[id].lengthLimit    = item.getAttribute('data-validate-length')       ? (item.getAttribute('data-validate-length')+'').split(',')       : false;
            this.data[id].minmax         = item.getAttribute('data-validate-minmax')       ? (item.getAttribute('data-validate-minmax')+'').split(',')       : false; // for type 'number', defines the minimum and/or maximum for the value as a number.
            this.data[id].validateLinked = item.getAttribute('data-validate-linked');

            return this.data[id];
        },

        /**
         * Find the closeset element, by selector
         * @param  {Object} el       [DOM node]
         * @param  {String} selector [CSS-valid selector]
         * @return {Object}          [Found element or null if not found]
         */
        closest : function(el, selector){
            var matchesFn;

            // find vendor prefix
            ['matches','webkitMatchesSelector','mozMatchesSelector','msMatchesSelector','oMatchesSelector'].some(function(fn){
                if( typeof document.body[fn] == 'function' ){
                    matchesFn = fn;
                    return true;
                }
                return false;
            })

            var parent;

            // traverse parents
            while (el) {
                parent = el.parentElement;
                if (parent && parent[matchesFn](selector)) {
                    return parent;
                }
                el = parent;
            }

            return null;
        },

        /**
         * MDN polyfill for Object.assign
         */
        extend : function( target, varArgs ){
            if( !target )
                throw new TypeError('Cannot convert undefined or null to object');

            var to = Object(target),
                nextKey, nextSource, index;

            for( index = 1; index < arguments.length; index++ ){
                nextSource = arguments[index];

                if( nextSource != null ) // Skip over if undefined or null
                    for( nextKey in nextSource )
                        // Avoid bugs when hasOwnProperty is shadowed
                        if( Object.prototype.hasOwnProperty.call(nextSource, nextKey) )
                            to[nextKey] = nextSource[nextKey];
            }

            return to;
        },

        /* Checks a single form item by it's type and specific (custom) attributes
        * {DOM Object}     - the item to be checked
        * {Boolean} silent - don't mark a item and only return if it passed the validation or not
        */
        checkitem : function( item, silent ){
            // skip testing items whom their type is not HIDDEN but they are HIDDEN via CSS.
            if( item.type !='hidden' && !item.clientWidth )
                return { valid:true, error:"" }

            item = this.filterFormElements( [item] )[0];

            // if item did not pass filtering or is simply not passed
            if( !item )
                return { valid:true, error:"" }

           // this.unmark( item );

            var linkedTo,
                testResult,
                optional = item.className.indexOf('optional') != -1,
                data = this.prepareitemData( item ),
                form = this.closest(item, 'form'); // if the item is part of a form, then cache it

            // check if item has any value
            /* Validate the item's value is different than the placeholder attribute (and attribute exists)
            *  this is needed when fixing the placeholders for older browsers which does not support them.
            *  in this case, make sure the "placeholder" jQuery plugin was even used before proceeding
            */

            // first, check if the item even has any value
            testResult = this.tests.hasValue.call(this, data.value);

            // if the item has value, check if that value is same as placeholder
            if( testResult === true )
                testResult = this.tests.sameAsPlaceholder.call(this, item, data );

            data.valid = optional || testResult === true;

            if( optional && !data.value ){
                return { valid:true, error:"" }
            }

            if( testResult !== true )
                data.valid = false;

            // validate by type of item. use 'attr()' is proffered to get the actual value and not what the browsers sees for unsupported types.
            if( data.valid ){
                testResult = this.testByType(item, data);
                data.valid = testResult === true ? true : false;
            }

            // if this item is linked to another item (their values should be the same)
            if( data.valid && data.validateLinked ){
                if( data['validateLinked'].indexOf('#') == 0 )
                    linkedTo = document.body.querySelector(data['validateLinked'])
                else if( form.length )
                    linkedTo = form.querySelector('[name=' + data['validateLinked'] + ']');
                else
                    linkedTo = document.body.querySelector('[name=' + data['validateLinked'] + ']');

                testResult = this.tests.linked.call(this, item.value, linkedTo.value, data.type );
                data.valid = testResult === true ? true : false;
            }

            if( !silent )
                this[data.valid ? "unmark" : "mark"]( item, testResult ); // mark / unmark the item

            return {
                valid : data.valid,
                error : data.valid === true ? "" : testResult
            };
        },

        /**
         * Only allow certain form elements which are actual inputs to be validated
         * @param  {HTMLCollection} form items Array [description]
         * @return {Array}                            [description]
         */
        filterFormElements : function( items ){
            var i,
                itemsToCheck = [];

            for( i = items.length; i--; ) {
                var isAllowedElement = items[i].nodeName.match(/input|textarea|select/gi),
                    isRequiredAttirb = items[i].hasAttribute('required'),
                    isDisabled = items[i].hasAttribute('disabled'),
                    isOptional = items[i].className.indexOf('optional') != -1;

                if( isAllowedElement && (isRequiredAttirb || isOptional) && !isDisabled )
                    itemsToCheck.push(items[i]);
            }

            return itemsToCheck;
        },

        checkAll : function( formElm ){
            if( !formElm ){
                console.warn('element not found');
                return false;
            }

            var that = this,
                result = {
                    valid  : true,  // overall form validation flag
                    items : []     // array of objects (per form item)
                },
                itemsToCheck = this.filterFormElements( formElm.elements );
                // get all the input/textareas/select items which are required or optional (meaning, they need validation only if they were filled)

            itemsToCheck.forEach(function(elm, i){
                var itemData = that.checkitem(elm);

                // use an AND operation, so if any of the items returns 'false' then the submitted result will be also FALSE
                result.valid = !!(result.valid * itemData.valid);

                result.items.push({
                    item   : elm,
                    error   : itemData.error,
                    valid   : !!itemData.valid
                })
            });

            return result;
        }
    }

    return FormValidator;
}));