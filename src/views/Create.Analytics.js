import React from "react"
import { getText } from "../functions"
import {countries} from "countries-list"

export default class Analytics extends React.Component {
    constructor(props) {
        super(props)
    }

    getFieldName(slug, type) {
        let ret = ""
        for( let field of this.props.fields[type] ) {
            if ( field.slug == slug ) {
                ret = field.name 
                break 
            }
        }

        return ret 
    }

    render() {
        var max = 4
        let mappings = this.props.channel.mappings.map( ( map, index ) => {
            if ( index > max ) {
                return ;
            } 
            return (
                <span key={index}>
                    <em><small>{`${this.getFieldName(map.productField, "product")} -> ${map.gField}`}</small></em><br />
                </span>
            )
        })

        let errorsEl = this.props.errors.map( (err, key) => {
            return (
                <div className="notice notice-error inline" key={key} style={{margin: 0}}><p>{err.message}</p></div>
            )
        })

        return (
            <form action="" className="muzaara-woopf-new no-mg" style={{width: "50%"}} onSubmit={this.props.create}>
                <div className="notice notice-info inline" style={{margin: 0}}>
                    <h3>Summary</h3>
                    <p>
                        {getText("channelName")}: <strong>{this.props.channel.name}</strong> &#65372; {getText("channelCountry")}: <strong>{countries[this.props.channel.country]["name"]}</strong> &#65372; {getText("pushType")}: <strong>{this.props.channel.pushType == 1 ? getText("pushToGoogle") : getText("pushURL")}</strong> &#65372; {getText("filters")}: <strong>{this.props.channel.filters.length}</strong> &#65372; {getText("rules")}: <strong>{this.props.channel.rules.length}</strong>
                    </p>
                    
                    <p>
                        {getText("fieldMapping")}:<br />
                        {mappings}
                        {mappings.length > max ? "..." : ""}
                    </p>
                </div>

                {errorsEl}
                <h2>{getText("googleAnalytics")}</h2>
                <div className="form-field">
                    <label htmlFor="campaignSource">{getText("campaignSource")} (utm_source)</label>
                    <input type="text" id="campaignSource" value={this.props.channel.utm.utm_source} onChange={this.props.update.bind(this, "utm_source")} />
                </div>
                <div className="form-field">
                    <label htmlFor="campaignMedium">{getText("campaignMedium")} (utm_medium)</label>
                    <input type="text" value={this.props.channel.utm.utm_medium} onChange={this.props.update.bind(this, "utm_medium")} id="campaignMedium" />
                </div>
                <div className="form-field">
                    <label htmlFor="campaignTerm">{getText("campaignTerm")} (utm_term)</label>
                    <input type="text" value={this.props.channel.utm.utm_term} onChange={this.props.update.bind(this, "utm_term")} id="campaignTerm" />
                </div>
                <div className="form-field">
                    <label htmlFor="campaignContent">{getText("campaignContent")} (utm_content)</label>
                    <input type="text" value={this.props.channel.utm.utm_content} onChange={this.props.update.bind(this, "utm_content")} id="campaignContent" />
                </div>
                <div className="form-field">
                    <label htmlFor="campaignCampaign">{getText("campaignCampaign")} (utm_campaign)</label>
                    <input type="text" value={this.props.channel.utm.utm_campaign} onChange={this.props.update.bind(this, "utm_campaign")} id="campaignCampaign" />
                </div>
                <div className="form-field">
                    <button type="submit" className="button-primary" disabled={this.props.isCreating}>
                        {getText(
                            this.props.isCreating ? (this.props.channel.id ? "savingChanges" : "creatingChannel") : (this.props.channel.id ? "saveChanges" : "createChannel") 
                        )}
                    </button>
                    &nbsp;&nbsp;
                    <button type="button" onClick={this.props.goBack} className="button-secondary">{getText("goBack")}</button>
                </div>
            </form>
        )
    }
}