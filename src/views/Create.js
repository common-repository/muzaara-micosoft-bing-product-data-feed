import React from "react"
import {getText, GoogleFieldsToSelect, FieldsToSelect} from "../functions"
import {countries} from "countries-list"
import Filters from "./Create.Filters"
import Rules from "./Create.Rules"
import Analytics from "./Create.Analytics"

export default class Create extends React.Component {
    constructor(props) {
        super(props)
    }

    render() {
        let ret = "";

        if ( this.props.ctrl.state.creationStep == 1 ) {
            ret = <First 
                    cancel={this.props.ctrl.acCancelNew} 
                    onChange={this.props.ctrl.updateSimpleFields} 
                    isLoading={this.props.ctrl.state.creatingLoading} 
                    onSubmit={this.props.ctrl.creationNextStep}
                    productTypes={this.props.ctrl.state.productTypes}
                    addProductType={this.props.ctrl.addProductType}
                    fields={this.props.ctrl.state.channel} />
        } else if ( this.props.ctrl.state.creationStep == 2 ) {
            ret = <Second 
                    fields={this.props.ctrl.state.fields}  
                    onChange={this.props.ctrl.updateMapping} 
                    values={this.props.ctrl.state.channel.mappings} 
                    removeMapping={this.props.ctrl.deleteMapping}
                    newMapping={this.props.ctrl.newMapping}
                    categories={this.props.ctrl.state.categories}
                    updateCategory={this.props.ctrl.updateCategoryMapping}
                    categoryMappings={this.props.ctrl.state.channel.categoryMappings}
                    filterConditions={this.props.ctrl.state.filterConditions}
                    filters={this.props.ctrl.state.channel.filters}
                    autocomplete={this.props.ctrl.state.gcatSearch}
                    activeCatMap={this.props.ctrl.state.activeCatMap}
                    filtersUpdate={this.props.ctrl.updateFilterRules}
                    addNewFilter={this.props.ctrl.addNewFilter}
                    deleteFilter={this.props.ctrl.deleteFilter}
                    editId={this.props.ctrl.state.channel.id}
                    selectAutocomplete={this.props.ctrl.selectAutocompleteResult}
                    rules={{
                        create: this.props.ctrl.addNewRule,
                        rules: this.props.ctrl.state.channel.rules,
                        update: this.props.ctrl.updateRule,
                        delete: this.props.ctrl.deleteRule
                    }}
                    errors={this.props.ctrl.state.errors}
                    goBack={this.props.ctrl.goBack}
                    nextStep={this.props.ctrl.creationNextStep}
                    categoryPassdown={this.props.ctrl.passDownCategory}
                    gfields={this.props.ctrl.state.gfields} />
        } else if ( this.props.ctrl.state.creationStep == 3 ) {
            ret = <Analytics 
                    create={this.props.ctrl.createChannel} 
                    isCreating={this.props.ctrl.state.creating} 
                    goBack={this.props.ctrl.goBack} 
                    channel={this.props.ctrl.state.channel} 
                    update={this.props.ctrl.updateUtm} 
                    errors={this.props.ctrl.state.errors}
                    fields={ { google: this.props.ctrl.state.gfields, product: this.props.ctrl.state.fields } } />
        } else if ( this.props.ctrl.state.creationStep == 4 ) {
            ret = <Done />
        }

        return(
            <div>
                {ret}
            </div>
        )
    }
}

const First = ({cancel, fields, onChange, isLoading, onSubmit, productTypes, addProductType}) => {
    let countriesEl = [<option value="" key={0}></option>]
    for( let c in countries ) {
        countriesEl.push(<option value={c} key={c}>{countries[c]["name"]}</option>)
    }

    let productTypesEl = []

    for ( let key in productTypes ) {
        productTypesEl.push(
            <li key={key} onClick={addProductType.bind(this, key)} className={ fields.productTypes.includes(key) ? "active" : ""}>{productTypes[key]}</li>
        )
    }

    return (
        <div className="new-design-form">
        <form action="" className="muzaara-woopf-new" onSubmit={onSubmit}>
            {fields.id ? ( <h2>{getText("editChannel" )}</h2> ) : "" }
            <div className="form-field">
                <label htmlFor="channelName">{getText("channelName")}</label>
                <input  onChange={onChange.bind(this, "name")} type="text" id="channelName" value={fields.name} required />
            </div>
            <div className="form-field">
                <label htmlFor="channelCountry">{getText("channelCountry")}</label>
                <select id="channelCountry" value={fields.country} onChange={onChange.bind(this, "country")} required>
                    {countriesEl}
                </select>
            </div>
            <div className="form-field">
                <label htmlFor="pushType">{getText("pushType")}</label>
                <select id="pushType" value={fields.pushType} onChange={onChange.bind(this, "pushType")}>
                    <option value={1}>{getText("pushToGoogle")}</option>
                    <option value={2}>{getText("pushURL")}</option>
                </select>
            </div>
            { fields.pushType == 2 ? 
            (
                <div className="form-field">
                    <label htmlFor="refreshRate">{getText("refreshRate")}</label>
                    <select id="refreshRate" value={fields.refreshRate} onChange={onChange.bind(this, "refreshRate")}>
                        <option value={24}>{getText("daily")}</option>
                        <option value={1}>{getText("hourly")}</option>
                        <option value={168}>{getText("weekly")}</option>
                    </select>
                </div>
            ) : (
                <React.Fragment>
                    <div className="form-field">
                        <label htmlFor="merchantId">{getText("merchantId")}</label>
                        <input type="text" required id="merchantId" value={fields.merchantId} onChange={onChange.bind(this, "merchantId" )} />
                    </div>
                    <div className="form-field">
                        <label htmlFor="catalogId">{getText("catalogId")}</label>
                        <input type="text" id="catalogId" value={fields.catalogId} onChange={onChange.bind(this, "catalogId" )} />
                    </div>
                    <div className="form-field">
                        <label htmlFor="noticeEmail">{getText( "noticeEmail" )}</label>
                        <input type="email" id="noticeEmail" value={fields.noticeEmail} onChange={onChange.bind(this, "noticeEmail" )} />
                    </div>
                </React.Fragment>
            ) }

            <div className="form-field">
                <label>{getText( "includeProductTypes" )}</label>
                <ul className="muzaara-woopf-product-types">
                    {productTypesEl}
                </ul>
            </div>
            
            <div className="form-field">
                <button className="button-primary" disabled={isLoading}>{getText("continue")}</button>&nbsp;&nbsp;<button className="button-secondary" onClick={cancel}>{getText("cancel")}</button>
            </div>
        </form>
        </div>
    )
}

const Second = ({
    fields, 
    gfields, 
    values, 
    onChange, 
    removeMapping, 
    newMapping, 
    categories, 
    updateCategory, 
    categoryMappings, 
    activeCatMap, 
    autocomplete, 
    selectAutocomplete,
    filterConditions,
    filters,
    filtersUpdate,
    addNewFilter,
    deleteFilter,
    rules,
    goBack,
    nextStep,
    errors,
    editId,
    categoryPassdown
}) => {
    let rows = [];
    let defaultMax = values.length // gfields.length >= 5  ? 5 : gfields.length ;
    let errorsEl = errors.map( (err, key) => {
        return (
            <div className="notice notice-error inline" key={key}><p>{err.message}</p></div>
        )
    })
    
    for( let i=0; i < defaultMax; i++ ) {
        rows.push(
            <tr valign="top" className={ i%2 ? "alternate" : "" } key={i}>
                <td scope="row">
                    <select value={values[i]["gField"]} onChange={onChange.bind(this, i, "gField" )}>      
                        <GoogleFieldsToSelect gfields={gfields} />
                    </select>
                </td>
                <td>
                    <input type="text" value={values[i]["prefix"]} onChange={onChange.bind(this, i, "prefix" )} placeholder={getText("prefix")} />
                </td>
                <td>
                    {values[i]["type"] ? (
                        <input type="text" required value={values[i]["productField"]} onChange={onChange.bind(this, i, "productField" )} />
                    ) : (
                        <select value={values[i]["productField"]} onChange={onChange.bind(this, i, "productField" )} required>
                            <FieldsToSelect fields={fields} />
                        </select>
                    )}
                    
                </td>
                <td>
                    <input type="checkbox" value={values[i]["type"]} checked={values[i]["type"]} onChange={onChange.bind(this, i, "type")} />
                </td>
                <td>
                    <input type="text" value={values[i]["suffix"]} onChange={onChange.bind(this, i, "suffix" )} placeholder={getText("suffix")} />
                </td>
                <td>
                    <span title={getText("remove")} className="dashicons dashicons-trash" style={{color: "#a00", cursor: "pointer"}} onClick={removeMapping.bind(this, i)}></span>
                </td>
            </tr>
        )
    }

    return (
        <form action="" onSubmit={nextStep}>
            {editId ? ( <h2>{getText("editChannel" )}</h2> ) : "" }
            <section className="muzaara-woopf-section">
                <a style={{fontSize: "large"}} onClick={goBack}><span className="dashicons dashicons-arrow-left-alt"></span> Go back</a>
                <h3>{getText("fieldMapping")}</h3>
                <table className="form-table">
                    <thead>
                        <tr>
                            <th className="row-title">{getText("googleFields")}</th>
                            <th>{getText("prefix")}</th>
                            <th>{getText("productField")}</th>
                            <th>{getText("freeText")}</th>
                            <th>{getText("suffix")}</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        {rows}
                    </tbody>
                </table>
                <button type="button" onClick={newMapping} className="button-secondary">{getText("addNewMapping")}</button>
            </section>
            <section style={{width: "60%"}} className="muzaara-woopf-section">
                <h3>{getText("categoryMapping")}</h3>
                <div className="notice notice-info inline" style={{ margin: 0, marginBottom: "10px" }}>
                    <p dangerouslySetInnerHTML={{ __html: getText("catMappingDesc")}}>
                        
                    </p>
                </div>
                <CategoryMapping 
                    categories={categories} 
                    update={updateCategory} 
                    active={activeCatMap}
                    autocomplete={autocomplete}
                    selectAutocomplete={selectAutocomplete}
                    passdown={categoryPassdown}
                    mappings={categoryMappings} />
            </section>
            <Filters filters={filters} delete={deleteFilter} addNew={addNewFilter} onUpdate={filtersUpdate} conditions={filterConditions} fields={fields} />
            <Rules addNew={rules.create} delete={rules.delete} conditions={filterConditions} fields={fields} update={rules.update} rules={rules.rules} />

            {errors.length ? (<div style={{marginBottom: "20px"}}>{errorsEl}</div>) : "" }
            <div className="muzaara-woopf-save">
                <button type="submit" className="button-primary">{getText("saveContinue")}</button>
                &nbsp;&nbsp;
                <button type="button" className="button-secondary" onClick={goBack}>{getText( "goBack")}</button>
            </div>
        </form>
    )
}


const presentCategories = (cats, parent, depth, mappings, update, active, results, passdown) => {
    let ret = []
    let children = cats.filter( value => value.parent == parent )
    if (!children.length) {
        return ret;
    }

    for( let i in children ) {
        let cat = children[i]
        ret.push(
            <tr key={cat.id} className={ i%2 ? "alternate" : "" }>
                <td scope="row" style={parent != 0 ? { paddingLeft: `${depth*20}px`} : {}}>
                    {(parent != 0 ? '- '.repeat(depth) : "") + cat.name} 
                    { typeof(mappings[cat.id]) != "undefined" && mappings[cat.id].trim() != "" ? <span className="dashicons dashicons-arrow-down-alt" style={{fontSize: "14px"}} onClick={passdown.bind(this, (parent==0?i:cat.id), mappings[cat.id], cat.parent != 0)}></span> : ""}
                    
                </td>
                <td>
                    <div style={{ width: "100%", position: "relative"}}>
                    <input value={mappings[cat.id]} style={{width: "100%"}} onChange={update.bind(this, cat.id)} type="search" placeholder={getText("enterToSearch")} />
                    
                    { cat.id == active ? (
                        <ul className="muzaara_woopf_bing_cat">
                            {results}
                        </ul>
                    ) : "" }
                    </div>
                </td>
                
            </tr>
        )

        ret = ret.concat(presentCategories(cats, cat.id, depth+1, mappings, update, active, results, passdown))
    }

    return ret;
}

const CategoryMapping = ({categories, update, mappings, active, autocomplete, selectAutocomplete, passdown}) => {
    let results = []
    for ( let id in autocomplete ) {
        results.push(
            <li style={{ padding: "3px 10px"}} onClick={selectAutocomplete.bind(this, id)} key={id}>{ id == 0 ? <em>{autocomplete[id]}</em> : autocomplete[id] }</li>
        )
    }

    return (
        <table className="widefat">
            <thead>
                <tr>
                    <th className="row-title">{getText("productCategory")}</th>
                    <th>{getText("googleCategory")}</th>
                </tr>
            </thead>
            <tbody>
                {presentCategories(categories, 0, 0, mappings, update, active, results, passdown)}
            </tbody>
        </table>
    )
}

const Done = () => {
    
    return(
        <div className="muzaara-woopf-create-complete">

        </div>
    )
}