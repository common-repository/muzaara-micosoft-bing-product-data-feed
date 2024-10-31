import React from "react"
import { RadioGroup, RadioButton } from 'react-radio-buttons'
import {getText} from "../functions"

const ListAccounts = ({accounts, selectAccount, selected, isLinking, linkError, link}) => {
    if ( accounts.length == 0 ) {
        return <span>{MUZAARA_WOOPF_BING.l10n.no_account_found}</span>
    } 

    let accountFields = accounts.map( ( a, i ) => {
        return(
            <label key={i} style={{ display: "block", marginBottom: "5px"}}>
                <input type="radio" onChange={selectAccount.bind(this, i)} checked={selected == i} defaultValue={a.Id} />
                <span>{a.Name} - {a.Number}</span>
            </label>
        )
    })

    return(
        <div>
            <fieldset>
                <legend style={{ fontSize: "1.3em", marginBottom: "10px"}}>{MUZAARA_WOOPF_BING.l10n.chooseAccount}</legend>
                {accountFields}
                <p>
                    <button className="button-primary" type="button" onClick={link} disabled={selected === null}>{ !isLinking ? getText("linkAccount") : getText("linking") }</button>
                </p>
            </fieldset>
            { linkError !== null ? 
                (   
                    <div>
                        <div className="notice notice-error inline">{getText("linkError")}</div> 
                        <p style={{ overflow: "scroll", height: "250px"}}><code>{JSON.stringify(linkError)}</code></p>
                    </div>
                ) : "" }
        </div>
    )
}

export const GoogleLogin = ({authenticating, onClick, accounts, selectAccount, selected, isLinking, linkError, link}) => {
    return (

        <div className="card">
            <h2 className="title">
                {MUZAARA_WOOPF_BING.l10n.linkGoogleDesc}
            </h2>
            <p>
                {MUZAARA_WOOPF_BING.l10n.linkGoogleDesc}
            </p>

            <div>
                { authenticating ? 
                    (
                         accounts == null ? (
                            <div className="spinner is-active" style={{
                                float: "none",
                                width: "auto",
                                height: "auto",
                                padding: "3px 25px"
                            }}>
                                {MUZAARA_WOOPF_BING.l10n.linkingAccount}
                            </div>
                        ) : (
                            <ListAccounts accounts={accounts} link={link} selectAccount={selectAccount} selected={selected} isLinking={isLinking} linkError={linkError} />
                        )
                        
                    ) : 
                    (
                        <div>
                        <a onClick={onClick} className="google-signin"></a>
                        { linkError !== null ? 
                            (   
                                <div>
                                    <div className="notice notice-error inline">{getText("linkError")}</div> 
                                    {/* <div className="notice notice-error inline">{linkError.message}</div>  */}
                                    <p style={{ overflow: "scroll", height: "250px"}}><code>{JSON.stringify(linkError)}</code></p>
                                </div>
                        ) : "" }
                        </div>
                    ) }
            </div>
        </div>
    )
}
