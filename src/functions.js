import React from "react"
import jsonToForm from "json-form-data"

export const getText = ( prop ) => {
    if ( typeof( MUZAARA_WOOPF_BING.l10n[prop]) == "undefined" ) 
        return ""

    return MUZAARA_WOOPF_BING.l10n[prop];
}

export const request = (options) => {
    if ( typeof(options.body) == "object" ) {
        options.body = jsonToForm(options.body)
    }
    options.credentials = "same-origin"
    return fetch( MUZAARA_WOOPF_BING.ajax, options );
}

const toForm = (obj, form) => {
    for ( const index in obj ) {
        let value = obj[index]
        if ( typeof(value) == "object" ) {
            form = toForm(value, form)
        } else {
            form.append(value, form)
        }
    }

    return form;
}

export const FieldsToSelect = ({fields, exclude}) => {
    let productFields = {}
    let key = 1
    for ( let field of fields ) {
        if (typeof(productFields[field.typeFriendly]) == "undefined" ) 
            productFields[field.typeFriendly] = []

        if ( typeof(exclude) != "undefined" && exclude == field.slug ) 
            continue

        productFields[field.typeFriendly].push(
            <option value={field.slug} key={key} data-field-type={field.type}>{field.name}</option>
        )

        key++
    }

    key = 1;
    let f = [<option value="" key={0}></option>]
    for( let k in productFields ) {
        f.push(
            <optgroup label={k} key={key}>
                {productFields[k]}
            </optgroup>
        )

        key++
    }

    return f
}

export const GoogleFieldsToSelect = ({gfields}) => {
    let el = {}
    let key = 0
    let ret = []

    for ( let field of gfields ) {
        if ( typeof(el[field.groupFriendly]) == "undefined" )
            el[field.groupFriendly] = []
        
        el[field.groupFriendly].push(
            <option value={field.slug} key={key}>{field.name}</option>
        )
        key++
    }

    key = 0
    for( let k in el ) {
        ret.push(
            <optgroup label={k} key={key}>
                {el[k]}
            </optgroup>
        )
        key++
    }

    return ret 
}