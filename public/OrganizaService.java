package com.iotits.wpb.service;

import com.iotits.common.form.DataGridListForm;
import com.iotits.core.config.IConfig;
import com.iotits.mybatis.service.AbstractService;
import com.iotits.wpb.bo.MemberBo;
import com.iotits.wpb.bo.OrganizaBo;
import com.iotits.wpb.model.WpbOrganiza;
import org.apache.commons.collections.MultiMap;
import org.apache.commons.lang3.StringUtils;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Service;

import java.util.*;

@Service
public class OrganizaService extends AbstractService<WpbOrganiza> {
	@Autowired
	private MemberService memberService;
    public static int flag=0;
	public Integer insert(WpbOrganiza record) {
		return super.insertSelective(record);
	}

	public Integer updateByPrimaryKeySelective(WpbOrganiza record) {
		return super.updateByPrimaryKeySelective(record);
	}

	public Integer deleteByPrimaryKey(Integer id) {
		return super.deleteByPrimaryKey(id);
	}

	public WpbOrganiza selectByPrimaryKey(Integer id) {
		return super.selectByPrimaryKey(id);
	}

	public DataGridListForm dataList(Map<String, Object> resultMap) {
		resultMap.put("where_sql", "status=1 and parentId=0");
		DataGridListForm ret = super.dataList(resultMap);
		List<OrganizaBo> rows = (List<OrganizaBo>) ret.getRows();
		List<Integer> Ids = new ArrayList<>();
		for (OrganizaBo row : rows) {
			Ids.add(row.getId());
		}
		if (Ids.size() > 0) {
			List<OrganizaBo> temp = getOrganizaByParentId(StringUtils.join(Ids.toArray(), ","));
			for (OrganizaBo row : temp) {
				rows.add(row);
			}
		}
		Map<Integer, List<OrganizaBo>> OrganizaInfo = getOrganizaMap(rows);
		ret.setRows(getOrganizaTree(OrganizaInfo, 0, 100));
		return ret;
	}

	public Map<Integer, List<OrganizaBo>> getOrganizaMap(List<OrganizaBo> rows) {
		Map<Integer, List<OrganizaBo>> OrganizaInfo = new HashMap<>();
		Map<Integer,List<MemberBo>> mem=new HashMap<>();
		for (OrganizaBo info : rows) {
			if (mem.containsKey(info.getId())){
				List<MemberBo> memberBos=mem.get(info.getId());

			}
			List<OrganizaBo> Organiza;
			if (OrganizaInfo.containsKey(info.getParentid())) {
				Organiza = OrganizaInfo.get(info.getParentid());
			} else {
				Organiza = new ArrayList<>();
			}
			Organiza.add(info);
			OrganizaInfo.put(info.getParentid(), Organiza);
		}
		return OrganizaInfo;
	}
	public Map<Integer,List<MemberBo>> getMemberMap(List<MemberBo> rows){
		Map<Integer,List<MemberBo>> MemberInfo=new HashMap<>();
		for (MemberBo memberBo:rows){
			List<MemberBo> memberBos;
			if (MemberInfo.containsKey(memberBo.getOrgid())){
				memberBos=MemberInfo.get(memberBo.getOrgid());
			}else {
				memberBos=new ArrayList<>();
			}
			memberBos.add(memberBo);
			MemberInfo.put(memberBo.getOrgid(),memberBos);
		}
		return MemberInfo;
	}
	public Map<Integer, List<OrganizaBo>> getOrgMem(List<OrganizaBo> rows,List<MemberBo> memberBos) {
		Map<Integer, List<OrganizaBo>> OrganizaInfo = new HashMap<>();
		Map<Integer,List<MemberBo>> MemberInfo=getMemberMap(memberBos);
		for (OrganizaBo info : rows) {
			if (MemberInfo.containsKey(info.getId())){
				info.setMembers(MemberInfo.get(info.getId()));
			}
			List<OrganizaBo> Organiza;
			if (OrganizaInfo.containsKey(info.getParentid())) {
				Organiza = OrganizaInfo.get(info.getParentid());
			} else {
				Organiza = new ArrayList<>();
			}
			Organiza.add(info);
			OrganizaInfo.put(info.getParentid(), Organiza);
		}
		return OrganizaInfo;

	}

	public List<OrganizaBo> getOrganizaByParentId(String ids) {
		Map<String, Object> params = new HashMap<>();
		params.put("order_sql", "order by parentId asc, sort desc");
		params.put("where_sql", "parentId in(" + ids + ")");
		List<OrganizaBo> result = super.findList(params);
		List<String> idList = new ArrayList<>();
		for (OrganizaBo info : result) {
			idList.add(info.getId().toString());
		}
		if (idList.size() > 0) {
			List<OrganizaBo> children = getOrganizaByParentId(StringUtils.join(idList, ","));
			for (OrganizaBo row : children) {
				result.add(row);
			}
		}
		return result;
	}

	public Integer deleteById() {
		return super.deleteByIds();
	}

	public List<WpbOrganiza> findAll(Map<String, Object> params) {
		if (params == null) {
			params = new HashMap<>();
			params.put("where_sql", "status=1");
		}
		List<WpbOrganiza> rows = super.findList(params);
		return rows;
	}

	public Map<Integer, WpbOrganiza> findByIds(List<Integer> ids) {
		Map<String, Object> params = new HashMap<>();
		params.put("where_sql", String.format("id in(%s)", StringUtils.join(ids, ",")));
		List<WpbOrganiza> rows = super.findList(params);
		Map<Integer, WpbOrganiza> ret = new HashMap<>();
		for (WpbOrganiza row : rows) {
			ret.put(row.getId(), row);
		}
		return ret;
	}

	public Map<Integer, WpbOrganiza> getMapInfo(Map<String, Object> params) {
		List<Object> whereList = new ArrayList<>();
		whereList.add("status=1");
		for (Map.Entry<String, Object> info : params.entrySet()) {
			if (info.getValue() instanceof Set || info.getValue() instanceof List) {
				whereList.add(info.getKey() + " in(" + StringUtils.join(((Set) info.getValue()).toArray(), ",") + ")");
			} else {
				whereList.add(info.getKey() + "=" + info.getValue());
			}
		}
		Map<String, Object> param = new HashMap<>();
		if (whereList.size() > 0) {
			param.put("where_sql", StringUtils.join(whereList, " and "));
		}
		Map<Integer, WpbOrganiza> rs = new HashMap<>();
		List<WpbOrganiza> dataInfo = this.findAll(param);
		if (dataInfo.size() > 0) {
			for (WpbOrganiza info : dataInfo) {
				rs.put(info.getId(), info);
			}
		}
		return rs;
	}

	public List<OrganizaBo> getParentList() {
		return getOrganizaTree(null, 0, Integer.parseInt(IConfig.get("organiza.level")));
	}
	public List<OrganizaBo> getOrgMem() {
		return getOrgMemData(null, 0, Integer.parseInt(IConfig.get("organiza.level")));
	}

	public void rmRrgById(List<OrganizaBo> orgInfo, Integer id) {
		Iterator<OrganizaBo> iterator = orgInfo.iterator();
		while (iterator.hasNext()) {
			OrganizaBo info = iterator.next();
			if (id.equals(info.getId())) {
				iterator.remove();
			} else if (null != info.getChildren() && info.getChildren().size() > 0) {
				rmRrgById(info.getChildren(), id);
			}
		}
	}

	private List<OrganizaBo> getOrganizaTree(Map<Integer, List<OrganizaBo>> OrganizaInfo, Integer parentId, Integer level) {
		if (level <= 0) {
			return null;
		}
		if (null == OrganizaInfo) {
			resultMap.put("order_sql", "order by parentId asc, sort desc");
			List<OrganizaBo> result = super.findList(resultMap);

			OrganizaInfo = getOrganizaMap(result);
		}
		List<OrganizaBo> data = new ArrayList<>();

		if (OrganizaInfo.containsKey(parentId))
			for (OrganizaBo info : OrganizaInfo.get(parentId)) {
			info.setText(info.getOrgname());
			info.setChildren(getOrganizaTree(OrganizaInfo, info.getId(), level--));
			data.add(info);
		}

		return data.size() > 0 ? data : null;
	}
	private List<OrganizaBo> getOrgMemData(Map<Integer, List<OrganizaBo>> OrganizaInfo,Map<Integer,List<MemberBo>> memberBoMap, Integer parentId, Integer level,Integer size) {
        if (level <= 0) {
            return null;
        }

        if (null == OrganizaInfo) {
            resultMap.put("order_sql", "order by parentId asc, sort desc");
            List<OrganizaBo> result = super.findList(resultMap);
            size=-result.size();
			List<MemberBo> memberBoList = memberService.findList(null);
			for (OrganizaBo info:result){
				if (memberBoMap.containsKey(info.getId())){
					List<OrganizaBo> data = new ArrayList<>();
					for (MemberBo memberBo:memberBoMap.get(info.getId())){
						OrganizaBo organizaBo = new OrganizaBo();
						organizaBo.setText(memberBo.getNickname());
						organizaBo.setId(memberBo.getId());
						data.add(organizaBo);
					}
					info.setChildren(data);
				}
			}
            OrganizaInfo = getOrgMem(result, memberBoList);
        }
        List<OrganizaBo> data = new ArrayList<>();
        if (OrganizaInfo.containsKey(parentId)) {
            for (OrganizaBo info : OrganizaInfo.get(parentId)) {
                info.setText(info.getOrgname());
                if (info.getChildren()!=null){
                	continue;
				}
                info.setChildren(getOrgMemData(OrganizaInfo,memberBoMap, info.getId(), level--,size--));
				info.setId(size);
                data.add(info);
            }

        }
        return data.size() > 0 ? data : null;
    }
	public List<OrganizaBo> getOrganizaList() {
		List<OrganizaBo> rows = getOrganizaByParentId("0");
		Map<Integer, List<OrganizaBo>> OrganizaInfo = getOrganizaMap(rows);
		return getOrganizaTree(OrganizaInfo, 0, 100);
	}

	public List<WpbOrganiza> getByParentId(Integer id) {
		Map<String, Object> params = new HashMap<>();
		params.put("where_sql", "status=1 and parentId=" + id);
		List<WpbOrganiza> rows = super.findList(params);
		return rows;
	}

	public List<OrganizaBo> orgListForApp(Integer id) {
		List<OrganizaBo> rows = getOrganizaTree(null, id, Integer.parseInt(IConfig.get("organiza.level")));
		for (OrganizaBo row : rows) {
			if (null == row.getChildren() || row.getChildren().size() == 0) {
				row.setLeaf(true);
			} else {
				row.setLeaf(false);
			}
		}
		return rows;
	}

	public List<OrganizaBo> orgNavForApp(Integer id) {
		Map<String, Object> params = new HashMap<>();
		List<OrganizaBo> list = new ArrayList<>();
		params.put("where_sql", "id=" + id);
		OrganizaBo info = super.findOne(params);
		if (null != info) {
			Integer maxLevel = Integer.parseInt(IConfig.get("organiza.level"));
			Integer currLevel = 0;
			list.add(info);
			while (true) {
				currLevel++;
				if (info.getParentid() == 0 || currLevel >= maxLevel) {
					break;
				}
				params.put("where_sql", "id=" + info.getParentid());
				info = super.findOne(params);
				list.add(info);
			}
		}
		List<OrganizaBo> ret = new ArrayList<>();
		for (int i = list.size()-1; i >= 0; i--) {
			ret.add(list.get(i));
		}
		return ret;
	}
}