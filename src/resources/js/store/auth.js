import { OK, UNPROCESSABLE_ENTITY } from "../util";

const state = {
  user: null,
  apiStatus: null,
  loginErrorMessages: null,
  registerErrorMessages: null,
}

const getters = {
  check: state => !! state.user,
  username: state => state.user ? state.user.name : "",
}

const mutations = {
  setUser(state, user) {
    state.user = user
  },
  setApiStatus (state, status) {
    state.apiStatus = status
  },
  setLoginErrorMessages(state, messages) {
    state.loginErrorMessages = messages
  }
}

const actions = {
  // 会員登録
  async register(context, data) {
    context.commit("setApiStatus", null)
    const response = await axios.post("/api/register", data)

    // 作成成功
    if(response.status === CREATED) {
      context.commit("setApiStatus", true)
      context.commit("setUser", response.data)
      return false
    }

    context.commit("setApiStatus", false)

    // バリデーションエラー
    if(response.status === UNPROCESSABLE_ENTITY) {
      context.commit("setRegisterErrorMessages", response.data.errors)
    } else {
      context.commit("error/setCode", response.status, { root: true })
    }
  },

  // ログイン
  async login(context, data) {
    context.commit("setApiStatus", null)
    const response = await axios.post("/api/login", data).catch(err => err.response || err)

    // ログイン成功時
    if(response.status === OK) {
      context.commit("setApiStatus", true)
      context.commit("setUser", response.data)
      return false
    }

    context.commit("setApiStatus", false)
    if(response.status === UNPROCESSABLE_ENTITY) {
      // バリデーションエラーの場合
      context.commit("setLoginErrorMessages", response.data.errors)
    } else {
      // エラーの場合
      context.commit("error/setCode", response.status, {root: true})
    }
  },

  // ログアウト
  async logout (context) {
    context.commit("setApiStatus", null)
    const response = await axios.post('/api/logout')

    if(response === OK) {
      context.commit("setApiStatus", true)
      context.commit('setUser', null)
      return false
    }

    context.commit("setApiStatus", false)
    context.commit("error/setCode", response.status, { root: true })
  },

  // ログインユーザーチェック
  async currentUser(context) {
    context.commit("setApiStatus", null)
    const response = await axios.get("/api/user");
    const user = response.data || null;

    if(response.status === OK) {
      context.commit("setApiStatus", true)
      context.commit("setUser", user)
      return false
    }

    context.commit("setApiStatus", false)
    context.commit("error/setCode", response.status, { root: true })
  }
}

export default {
  namespaced: true,
  state,
  getters,
  mutations,
  actions
}
