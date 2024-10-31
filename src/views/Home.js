import React from "react"
import {getText} from "../functions"
import {GoogleLogin} from "./Login"
import {countries} from "countries-list"
import { Link } from "react-router-dom"

export default class Home extends React.Component {
	constructor(props) {
		super(props)

		this.state = {
			authenticating : false,
			accounts : null,
			selectedAccount: null,
			linkingError: null,
			linking: false
		}

		this.checkAuth = this.checkAuth.bind(this)
		this.getAccounts = this.getAccounts.bind(this)
		this.chooseAccount = this.chooseAccount.bind(this)
		this.linkAccount = this.linkAccount.bind(this)        
	}

	openNewTab() {
		this.setState({ authenticating : true }, () => {
			let t;
			let authWindow = window.open(MUZAARA_WOOPF_BING.oauthUrl, "muzaara-woopf-auth", "status=1,width=500,height=700")

			t = setInterval(() => {
				if ( authWindow.closed ) {
					clearInterval(t)
					this.checkAuth()
				}
			}, 500);
		})
	}

	chooseAccount(index) {
		if ( index < this.state.accounts.length ) {
			this.setState({selectedAccount: index})
		}
	}

	checkAuth() {
		let form = new FormData()
		form.append( "action", "muzaara_woopf_bing_checkAuth" )

		fetch(MUZAARA_WOOPF_BING.ajax, {
			credentials : "same-origin",
			method: "POST",
			body: form
		}).then( response => response.json() )
		.then(json => {
			if ( json.success ) {
				this.props.ctrl.checkReady()
				.then( status => {
					if ( !status ) {
						this.getAccounts()
					}
				})
				// this.getAccounts()
			} else {
				this.setState({authenticating: false})
			}
		})
	}

	linkAccount() {
		this.setState({linking: true, linkingError: null}, () => {
			let form = new FormData()
			form.append( "action", "muzaara_woopf_bing_linkAccount" )
			form.append( "account_id", this.state.accounts[this.state.selectedAccount].Id )

			fetch( MUZAARA_WOOPF_BING.ajax, {
				credentials: "same-origin",
				method: "POST", 
				body: form
			})
			.then( response => response.json() ) 
			.then( json => {
				if ( json.success ) {
					this.props.ctrl.checkReady()
				} else {
					this.setState({linking: false, linkingError: json.data })
				}
			})
		})
	}
	
	getAccounts() {
		this.setState({linkingError: null}, () => {
			let form = new FormData()
			form.append( "action", "muzaara_woopf_bing_getAccounts" )

			fetch(MUZAARA_WOOPF_BING.ajax, {
				credentials: "same-origin",
				method: "POST",
				body: form
			})
			.then( response => response.json() )
			.then(json => {
				if ( json.success ) {
					let accounts = json.data.accounts;
					this.setState({ accounts })
				} else {
					this.setState({ authenticating : false, linkingError: json.data, linking: false })
				}
			})
		})
	}

	render() {
		return(
			<div className="wrap">
				<h2>{MUZAARA_WOOPF_BING.l10n.parentHeader}</h2>
				{ this.props.ctrl.state.isReady ? (
					<div style={{marginTop: "3%"}}>
						<TableOptions createNew={this.props.ctrl.acCreateNew} />
						<Table channels={this.props.ctrl.state.channels} pause={this.props.ctrl.pauseChannel} remove={this.props.ctrl.deleteChannel} runNow={this.props.ctrl.runChannel } />
					</div>
				) : <GoogleLogin selectAccount={this.chooseAccount} selected={this.state.selectedAccount} onClick={this.openNewTab.bind(this)} authenticating={this.state.authenticating} accounts={this.state.accounts} link={this.linkAccount} isLinking={this.state.linking} linkError={this.state.linkingError} /> }
			</div>
		)
	}
}

const TableOptions = ({createNew}) => {
	return (
		<div style={{marginBottom: "20px"}}>
			<button type="button" className="button-primary" onClick={createNew}>{getText("createNew")}</button>
		</div>
	)
}

const Table = ({channels, pause, remove, runNow }) => {
	let channelsEl = channels.map( (channel, key) => {
		let postTypes = Object.keys(channel.product_types).map( ( type, key ) => {
			return <span className="muzaara-woopf-post-type" key={key}>{channel.product_types[type]}</span>
		})

		return (
			<tr key={key} className={ key % 2 ? "alternate" : "" }>
				<td className="title column-title has-row-actions column-primary page-title">
					<strong>{channel.name}</strong>
					<div className="row-actions">
						{channel.push_type == 2 && channel.is_active && !channel.is_running ? (
							<span className="run" onClick={runNow.bind(this, key)}>
								<a href="">{getText("runNow")}</a> | &nbsp;
							</span>
						) : "" }
						<span className="edit">
							<Link to={`edit/${channel.id}`}>{getText("edit")}</Link> | &nbsp;
						</span>
						<span className="edit">
							<a href="" onClick={pause.bind(this, key)}>{ channel.is_active ? getText( "pause" ) : getText( "resume" )}</a> | &nbsp;
						</span>
						<span className="trash">
							<a href="" onClick={remove.bind(this, key)}>Delete</a>
						</span>
					</div>
				</td>
				<td>
					<span title={ channel.is_active ? getText( ( channel.is_running ? "running" : "active" ) ) : getText( "paused" )} className={ "muzaara-woopf-channel-status " + ( channel.is_active ? ( channel.is_running ? "running" : "active" ) : "") }></span>
				</td>

				<td>
					<code id="dump_url">{channel.push_type == 1 ? channel.merchantId : channel.dump_url}</code> <span className="dashicons dashicons-admin-page" style={{cursor: "pointer"}} title="Copy" onClick={( e ) => {
						e.target.style.color = "rgba(70, 180, 80, 1)"
						var target = e.target
						navigator.clipboard.writeText(document.getElementById("dump_url").textContent)
						.then(() => setTimeout(function() { target.style.color = "inherit"}, 2000 ))
						
					}}></span>
				</td>
				<td>{countries[channel.country]["name"]}</td>
				<td>{postTypes}</td>
				<td>{channel.push_type == 1 ? getText("pushToGoogle") : getText( "pushURL" ) }</td>
				<td>{channel.total_products }</td>
				<td>{ channel.refresh_rate ? getText( "everyHours" ).replace("%d", channel.refresh_rate) : "" }</td>
				<td>{channel.last_refreshed}</td>
				<td>{channel.next_refresh}</td>
				<td>{channel.date_created}</td>
			</tr>
		)
	})

	return(
		<table className="widefat">
			<thead>
				<tr>
					<th className="row-title">{getText("channelName")}</th>
					<th>{getText("status")}</th>
					<th>{getText("dumpURL")}</th>
					<th>{getText("country")}</th>
					<th>{getText("productTypes")}</th>
					<th>{getText("pushType")}</th>
					<th>{getText("totalProducts")}</th>
					<th>{getText("refreshRate")}</th>
					<th>{getText("lastRefreshed")}</th>
					<th>{getText("nextRefresh")}</th>
					<th>{getText("dateCreated")}</th>
				</tr>
			</thead>
			<tbody>
				{ channels.length == 0 ? (
					<tr>
						<td colSpan={10} style={{textAlign: "center"}}>{getText("noChannels")}</td>
					</tr>
				) : channelsEl }
			</tbody>
			<tfoot>
				<tr>
					<th className="row-title">{getText("channelName")}</th>
					<th>{getText("status")}</th>
					<th>{getText("dumpURL")}</th>
					<th>{getText("country")}</th>
					<th>{getText("productTypes")}</th>
					<th>{getText("pushType")}</th>
					<th>{getText("totalProducts")}</th>
					<th>{getText("refreshRate")}</th>
					<th>{getText("lastRefreshed")}</th>
					<th>{getText("nextRefresh")}</th>
					<th>{getText("dateCreated")}</th>
				</tr>
			</tfoot>
		</table>
	)
}