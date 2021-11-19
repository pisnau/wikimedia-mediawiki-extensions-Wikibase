import Vuex, { Store } from 'vuex';
import Application from './Application';
import Vue from 'vue';
import { TrackFunction } from '@/@types/TrackingOptions';
import actions from '@/store/actions';
import { mutations } from '@/store/mutations';
import { getters } from '@/store/getters';
Vue.use( Vuex );

export function createStore( metricTracker: TrackFunction ): Store<Application> {
	const state: Application = {
		statementsTaintedState: { },
		statementsPopperIsOpen: { },
		statementsEditState: { },
		helpLink: '',
	};
	return new Store( {
		state,
		actions: actions( metricTracker ),
		mutations,
		getters,
	} );
}
