using System;
using System.IO;
using System.Data;
using System.Text;
using System.Collections.Generic;
using System.Text.RegularExpressions;
using MySql.Data.MySqlClient;

namespace TA.Booru.Web
{
    public class MySQLWrapper : IDisposable
    {
        private MySqlConnection _Connection = null;

        public MySQLWrapper(string Server, string Username, string Password, string Database)
        {
            string connectionString = string.Format("Server={0},Uid={1},Pwd={2},Database={3}", Server,Username,Password,Database);
            _Connection=new MySqlConnection(connectionString);
            _Connection.Open();
        }

        public ulong GetLastInsertedID()
        {
            using (var command = _Connection.CreateCommand())
            {
                command.CommandText = "SELECT last_insert_id()";
                return Convert.ToUInt64(command.ExecuteScalar());
            }
        }

        public DataTable ExecuteTable(string SQL, params object[] Args)
        {
            using (var command = _Connection.CreateCommand())
            {
                command.CommandText = SQL;
                command.Prepare();
                foreach (object arg in Args)
                {
                    var param = command.CreateParameter();
                    param.Value = arg;
                    command.Parameters.Add(param);
                }
                DataTable dt = new DataTable();
                dt.Load(command.ExecuteReader());
                return dt;
            }
        }

        public DataRow ExecuteRow(string SQL, params object[] Args)
        {
            using (DataTable table = ExecuteTable(SQL, Args))
                if (table.Rows.Count > 0)
                    return table.Rows[0];
                else return null;
        }

        public int ExecuteNonQuery(string SQL, params object[] Args)
        {
            using (var command = _Connection.CreateCommand())
            {
                command.CommandText = SQL;
                command.Prepare();
                foreach (object arg in Args)
                {
                    var param = command.CreateParameter();
                    param.Value = arg;
                    command.Parameters.Add(param);
                }
                return command.ExecuteNonQuery();
            }
        }

        public ulong ExecuteInsert(string TableName, Dictionary<string, object> Dictionary)
        {
            if (Dictionary.Count > 0)
            {
                //TODO X Test ExecuteInsert
                StringBuilder statement = new StringBuilder();
                statement.AppendFormat("INSERT INTO {0} ({1}) VALUES(", TableName, string.Join(", ", Dictionary.Keys));
                for (int i = 0; i < Dictionary.Count; i++)
                {
                    if (i > 0)
                        statement.Append(", ?");
                    else statement.Append('?');
                }
                statement.Append(')');
                using (var command = _Connection.CreateCommand())
                {
                    command.CommandText = statement.ToString();
                    command.Prepare();
                    foreach (object arg in Dictionary.Values)
                    {
                        var param = command.CreateParameter();
                        param.Value = arg;
                        command.Parameters.Add(param);
                    }
                    command.ExecuteNonQuery();
                    return GetLastInsertedID();
                }
            }
            else throw new ArgumentException("Dictionary must contain things");
        }

        public T ExecuteScalar<T>(string SQL, params object[] Args)
        {
            using (var command = _Connection.CreateCommand())
            {
                command.CommandText = SQL;
                command.Prepare();
                foreach (object arg in Args)
                {
                    var param = command.CreateParameter();
                    param.Value = arg;
                    command.Parameters.Add(param);
                }
                object retObj = command.ExecuteScalar();
                return (T)Convert.ChangeType(retObj, typeof(T));
            }
        }

        public void Dispose()
        {
            _Connection.Close();
            _Connection.Dispose();
        }
    }
}
