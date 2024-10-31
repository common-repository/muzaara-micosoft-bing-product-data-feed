import React from "react"
import {getText, FieldsToSelect} from "../functions"

export default class Rules extends React.Component {
    constructor(props) {
        super(props)
    }

    render() {
        let conditions = this.props.conditions.map( (condition, index) => {
            return (
                <option value={condition.condition} key={index}>{condition.name}</option>
            )
        })

        let rules = this.props.rules.map( ( rule, key ) => {
            return (
                <tr key={key}>
                    <td>
                        <select value={rule.if} onChange={this.props.update.bind(this, key, "if" )} required>
                            <FieldsToSelect fields={this.props.fields} />
                        </select>
                    </td>
                    <td>
                        <select value={rule.condition} onChange={this.props.update.bind(this, key, "condition")} required>
                            <option value=""></option>
                            {conditions}
                        </select>
                    </td>
                    <td>
                        {rule.valueType == 0 ? (
                            <select value={rule.value} onChange={this.props.update.bind(this, key, "value")}>
                                <FieldsToSelect fields={this.props.fields} exclude={rule.if} />
                            </select>
                        ) : (
                            <input type="text" value={rule.value} onChange={this.props.update.bind(this, key, "value")} />
                        )}
                        
                    </td>
                    <td>
                        <input type="checkbox" value={rule.valueType} onChange={this.props.update.bind(this, key, "valueType")} checked={rule.valueType} />
                    </td>
                    <td>
                        <select value={rule.then} onChange={this.props.update.bind(this, key, "then")} required>
                            <FieldsToSelect fields={this.props.fields}/>
                        </select>
                    </td>
                    <td>
                        {rule.isType == 0 ? (
                            <select value={rule.is} onChange={this.props.update.bind(this, key, "is")}>
                                <FieldsToSelect fields={this.props.fields} exclude={rule.then} />
                            </select>
                        ) : (
                            <input type="text" value={rule.is} onChange={this.props.update.bind(this, key, "is")} />
                        )}
                        
                    </td>
                    <td>
                        <input type="checkbox" checked={rule.isType} onChange={this.props.update.bind(this, key, "isType")} value={rule.isType} />
                    </td>
                    <td>
                        <span title={getText("remove")} onClick={this.props.delete.bind(this, key)} className="dashicons dashicons-trash" style={{color: "#a00", cursor: "pointer"}}></span>
                    </td>
                </tr>
            )
        })
        return (
            <section className="muzaara-woopf-section">
                <h3>{getText("rules")}</h3>
                <table className="widefat">
                    <thead>
                        <tr>
                            <th className="row-title">{getText("if")}</th>
                            <th>{getText("condition")}</th>
                            <th>{getText("value")}</th>
                            <th>{getText("freeText")} ({getText("value")})</th>
                            <th>{getText("then")}</th>
                            <th>{getText("becomes")}</th>
                            <th>{getText("freeText")} ({getText("becomes")})</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        {rules.length ? rules : (
                            <tr>
                                <td colSpan={8} style={{textAlign: "center"}}>{getText("noRules")}</td>
                            </tr>
                        )}
                    </tbody>
                </table>
                <button className="button-secondary" type="button" onClick={this.props.addNew}>
                    {getText("addRule")}
                </button>
            </section>
        )
    }
}