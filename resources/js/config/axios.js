import Vue from 'Vue'
import axios from 'axios'

const instance = axios.create({
    baseURL: 'http://localhost:8000' // put base or your app might concatenate the url
  })

instance.interceptors.request.use(config => {
    return config
})

instance.interceptors.response.use(response => {
    return response
})
export default instance
