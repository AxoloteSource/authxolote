import { ApisEnum } from '@/configs/apisEnum'
import { useGET, usePOST } from '@/hooks/useApi'
import { IPaginate } from '@/interfaces/IPaginate'
import { IPaginateServiceProps } from '@/interfaces/IPaginateServiceProps'
import { IUser } from '@/interfaces/models/User/user.interface'

const url = '/api/v1/users'
const urlLogin = ApisEnum.BaseLogin

export const useServiceIndexUsers = ({ filters = [], search = null, page = 1, limit = 10 }: IPaginateServiceProps) => {
  return useGET<IPaginate<IUser>>({
    url,
    customHost: urlLogin,
    filters: {
      filters,
      search,
      page,
      limit
    }
  })
}

export const useServiceStoreUser = () => usePOST<IUser>({ url, customHost: urlLogin })
